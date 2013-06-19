/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <iostream>
#include <fstream>
#include "Bruinbase.h"
#include "SqlEngine.h"

#include "BTreeIndex.h"

using namespace std;

// external functions and variables for load file and sql command parsing 
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}

RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning

  RC     rc;
  int    key;     
  string value;
  int    count;
  int    diff;

  bool useIndex = false;
  bool indexExists = false;
  int numForIndex = 0;
  vector<SelCond> eqCond;

  // Attempt to open the index
  BTreeIndex tree;
  rc = tree.open(table + ".idx", 'r');
  if (rc == 0)
  { indexExists = true; }
  vector<SelCond> indCond;

  // The bounds of the search and whether to include the bounds in the results
  int small = -1;
  int big = -1;
  bool ge = false;
  bool le = false;
  
  // Check to see if the conditions are favorable
  if (indexExists == true)
  {
     for (unsigned int i = 0; i < cond.size(); i++)
     {
        // Key condition
        if (cond[i].attr == 1)
        {
           int val;
           switch (cond[i].comp)
           {
             case SelCond::EQ:
              eqCond.push_back(cond[i]);
              numForIndex++;
              break;
             case SelCond::NE:
              indCond.push_back(cond[i]);
              break;
             case SelCond::GT:
              val = atoi(cond[i].value);
              if (small == -1 || val >= small)
              {
                 small = val;
                 ge = false;
              }
              numForIndex++;
              break;    
             case SelCond::LT:
              val = atoi(cond[i].value);
              if (big == -1 || val <= big)
              {
                 big = val;
                 le = false;
              }
              numForIndex++;
              break;
             case SelCond::GE:
              val = atoi(cond[i].value);
              if (small == -1 || val > small)
              {
                 small = val;
                 ge = true;
              }
              numForIndex++;
              break;
             case SelCond::LE:
              val = atoi(cond[i].value);
              if (big == -1 || val < big)
              {
                 big = val;
                 le <= false;
              }
              numForIndex++;
              break;
           } // End SWITCH
        } else
        {
           // Condition on value - we deal with it on a tuple by tuple basis
           indCond.push_back(cond[i]);
        }
     } // End FOR 
  } else
  {
     // No index exists
     goto noindex;
  }

  // No favorable conditions -> don't use index
  if (numForIndex == 0)
  { goto noindex; }
  else
  {
     int prev = -1;
     // Make sure all eq conditions are the same
     // And the eq condition is within the range
     for (unsigned int i = 0; i < eqCond.size(); i++)
     {
        int val = atoi(eqCond[i].value);
        if (prev != -1 && val != prev)
        {
           count = 0;
           goto countstar;
        } else if (((big != -1) || (val <= big && le == true || val < big && le == false)) && ((small == -1) || (val >= small && ge == true || val > small && ge == false)))
        {
           count = 0;
           goto countstar;
        }
        prev = val;
     }

     // Set the upper and lower bounds to the equal condition
     if (eqCond.size() > 0)
     {
        small = atoi(eqCond[0].value);
        big = atoi(eqCond[0].value);
        le = true;
        ge = true;
     }

     // Use the tree
     tree.readMetadata();

     count = 0;
     if (small == -1) { small = 0; }
 
     // Locate the smaller bound
     IndexCursor ic;
     tree.locate(small, ic);

     int retKey;
     RecordId retRid;
     rc = tree.readForward(ic, retKey, retRid);
    
     // If it's a greater than condition, read forward until key != lower bound
     if (ge == false)
         while (rc == 0 && retKey == small)
            tree.readForward(ic, retKey, retRid);

     if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
      fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
      return rc;
     }
  
     // Read until: no more tuples or out of range
     while (rc == 0 && (big == -1 || (retKey < big && le == false || retKey <= big && le == true)))
     {
      if ((rc = rf.read(retRid, key, value)) < 0) {
  	fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
	goto exit_select;
      }
      
    // check the conditions on the tuple
      for (unsigned i = 0; i < indCond.size(); i++) {
    // compute the difference between the tuple value and the condition value
	switch (indCond[i].attr) {
	case 1:
	  diff = key - atoi(indCond[i].value);
	  break;
	case 2:
	  diff = strcmp(value.c_str(), indCond[i].value);
	  break;
	}

	// skip the tuple if any condition is not met
	switch (indCond[i].comp) {
	case SelCond::EQ:
	  if (diff != 0) goto next_record;
	  break;
	case SelCond::NE:
	  if (diff == 0) goto next_record;
	  break;
	case SelCond::GT:
	  if (diff <= 0) goto next_record;
	  break;
	case SelCond::LT:
	  if (diff >= 0) goto next_record;
	  break;
	case SelCond::GE:
	  if (diff < 0) goto next_record;
	  break;
	case SelCond::LE:
	  if (diff > 0) goto next_record;
	  break;
	}
      }

      // the condition is met for the tuple. 
      // increase matching tuple counter
      count++;

      // print the tuple 
      switch (attr) {
      case 1:  // SELECT key
	fprintf(stdout, "%d\n", key);
	break;
      case 2:  // SELECT value
	fprintf(stdout, "%s\n", value.c_str());
	break;
      case 3:  // SELECT *
	fprintf(stdout, "%d '%s'\n", key, value.c_str());
	break;
      }

      next_record:
        rc = tree.readForward(ic, retKey, retRid);
     } // End WHILE

     goto countstar;
  }
  
 noindex:
  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }

  // scan the table file from the beginning
  rid.pid = rid.sid = 0;
  count = 0;

    while (rid < rf.endRid()) {
      // read the tuple
      if ((rc = rf.read(rid, key, value)) < 0) {
  	fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
	goto exit_select;
      }
      
    // check the conditions on the tuple
      for (unsigned i = 0; i < cond.size(); i++) {
    // compute the difference between the tuple value and the condition value
	switch (cond[i].attr) {
	case 1:
	  diff = key - atoi(cond[i].value);
	  break;
	case 2:
	  diff = strcmp(value.c_str(), cond[i].value);
	  break;
	}

	// skip the tuple if any condition is not met
	switch (cond[i].comp) {
	case SelCond::EQ:
	  if (diff != 0) goto next_tuple;
	  break;
	case SelCond::NE:
	  if (diff == 0) goto next_tuple;
	  break;
	case SelCond::GT:
	  if (diff <= 0) goto next_tuple;
	  break;
	case SelCond::LT:
	  if (diff >= 0) goto next_tuple;
	  break;
	case SelCond::GE:
	  if (diff < 0) goto next_tuple;
	  break;
	case SelCond::LE:
	  if (diff > 0) goto next_tuple;
	  break;
	}
      }

      // the condition is met for the tuple. 
      // increase matching tuple counter
      count++;

      // print the tuple 
      switch (attr) {
      case 1:  // SELECT key
	fprintf(stdout, "%d\n", key);
	break;
      case 2:  // SELECT value
	fprintf(stdout, "%s\n", value.c_str());
	break;
      case 3:  // SELECT *
	fprintf(stdout, "%d '%s'\n", key, value.c_str());
	break;
      }

      // move to the next tuple
    next_tuple:
      ++rid;
    }

 countstar:
  // print matching tuple count if "select count(*)"
  if (attr == 4) {
    fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  exit_select:
  rf.close();
  tree.close();
  return rc;
}

RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */
  
  // Attempt to open the file for reading
  fstream fs;
  const char *lf = loadfile.c_str();
  fs.open(lf, fstream::in);

  // Check if opening the file failed
  if (fs.fail() == true)
  {
     fs.close();
     return RC_FILE_OPEN_FAILED;
  }
  
  // Create a record file and attempt to open the table file
  RecordFile rf;
  RC rc = rf.open(table + ".tbl", 'w');  

  if (rc != 0)
  {
    fs.close();
    rf.close();
    return rc;
  }

  BTreeIndex tree;


  // Create a B+ tree
  if (index == true)
  { 
     
     rc = tree.open(table + ".idx", 'w');
 
     if (rc != 0)
     {
       
       // DEBUG 
       // printf("load: Could not create the index file\n");


        fs.close();
        rf.close();
        tree.close();
        return rc;
     }

     rc = tree.readMetadata();
     
     if (rc != 0)
     {
        fs.close();
        rf.close();
        tree.close();
        return rc;
     }
  }

  // Read lines
  string line;
  while (fs.eof() == false)
  {
    // Use '\n' as delimiter
    getline(fs, line);

    // Check if getting the next line failed
    if (fs.fail() == true)
    {
       fs.close();
       rf.close();
       if (index == true)
       {
          tree.writeMetadata();
          tree.close();
       }
       return RC_FILE_READ_FAILED;
    }

    int key;
    string value;
    rc = parseLoadLine(line, key, value);

    // Check if there was an error parsing the line
    if (rc != 0)
    {
       fs.close();
       rf.close();
       if (index == true)
       {
          tree.writeMetadata();
          tree.close();
       }
       return rc;
    }

    // Append the line to the table
    RecordId rid;
    rc = rf.append(key, value, rid);

    // Check if there was an error appending
    if (rc != 0)
    {
       fs.close();
       rf.close();
       if (index == true)
       {
          tree.writeMetadata();
          tree.close();
       }
       return rc;
    }
  
    // Insert into B+ tree
    if (index == true)
    {
       rc = tree.insert(key, rid);
  
       if (rc != 0)
       {
          fs.close();
          rf.close();
          tree.writeMetadata();
          tree.close();
          return rc;
       }
    }
  } 

  // Close the file
  fs.close();
  rf.close();

  if (index == true)
  {
    tree.writeMetadata();
    tree.close();
  }
  
  return 0;
}

RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
    const char *s;
    char        c;
    string::size_type loc;
    
    // ignore beginning white spaces
    c = *(s = line.c_str());
    while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
    key = atoi(s);

    // look for comma
    s = strchr(s, ',');
    if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
    do { c = *++s; } while (c == ' ' || c == '\t');
    
    // if there is nothing left, set the value to empty string
    if (c == 0) { 
        value.erase();
        return 0;
    }

    // is the value field delimited by ' or "?
    if (c == '\'' || c == '"') {
        s++;
    } else {
        c = '\n';
    }

    // get the value string
    value.assign(s);
    loc = value.find(c, 0);
    if (loc != string::npos) { value.erase(loc); }

    return 0;
}
