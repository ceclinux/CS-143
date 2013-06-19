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


bool should_use_index(RC index_open, const vector<SelCond>& cond) {

  bool default_options = index_open == 0 && cond.size() != 0;
  if (default_options == false) return false;
  
  // Go through the conditions and see what conditions we have
  
  for(unsigned int i = 0; i < cond.size(); i++) {
    
    if(cond[i].attr == 2) 
      return false;
    

    switch (cond[i].comp) {
    case SelCond::GT:
    case SelCond::LT:
    case SelCond::GE:
    case SelCond::LE:
    case SelCond::EQ:
      return true;
      break;
    case SelCond::NE:
      return false;
      break;
    }

  }
  
  return false;

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

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }

  // scan the table file from the beginning
  rid.pid = rid.sid = 0;
  count = 0;

  // Try to open up the index
  BTreeIndex index;
  RC index_open = index.open(table + ".idx", 'r');
  index.readMetadata();
  
  // DEBUG 
  //  printf("select: index_open = %d\n", index_open);
  


  // If we don't have an index, then do it the old school way.
  if( !should_use_index(index_open, cond) ) {
  
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

  }else {
    
    // We have an index, so let's use it!
    
    // DEBUG 
    //    printf("USING INDEX!\n");


    // keep a list of RecordIds to print out.
    vector<RecordId> to_print;
    RC read_status;
    RC search_status;
    
    // Figure out the conditions
    // Figure out which attribute we are looking for: 1 = key, 2 = value
    
    // check the conditions on the tuple
    for (unsigned i = 0; i < cond.size(); i++) {
      
      int compare_int_value = -1;
      char* compare_string_value;

      switch (cond[i].attr) {
      case 1:
	compare_int_value =  atoi(cond[i].value);
	compare_string_value = NULL;
	break;
      case 2:
	compare_string_value =  cond[i].value;
	compare_int_value = -1;
	break;
      }
      
      
      IndexCursor cursor;
      int key;
      RecordId rid;	
      RC smallest_search_status;
      IndexCursor startCursor;
      int smallestKey;
	
      switch (cond[i].comp) {
       
      case SelCond::EQ:
	// locate the correct tuple and then print it out
	
	// TODO: for now, if this is checking for value equality, then jus do it the old school way
	if(cond[i].attr == 2) {
	  

	}
	  
	
	search_status = index.locate(compare_int_value, cursor);
	
	// If we couldn't find a match just keep going and forget about this condition
	if (search_status != 0)
	  continue;
	
	
	// Now read the record id, and add the tuple to the toPrint vector

	read_status = index.readForward(cursor, key, rid);
	
	if(read_status != 0) {
	  // DEBUG:
	  //	  printf("SqlEngine.select: couldn't read the key in EQ!");
	  continue;
	}

	to_print.push_back(rid);
	break;
	

      case SelCond::GT:
	// locate the key-tuple and then print everything until then but using readforward
	search_status = index.locate(compare_int_value, cursor);
	
	// If we couldn't find a match just keep going and forget about this condition
	if (search_status != 0)
	  continue;
	

	// NOTE: Beacuse this is GREATER THAN, we will skip the equal record
	// Now read the record id, and add the tuple to the to_print vector
	
	read_status = index.readForward(cursor, key, rid);
	
	if(read_status != 0) {
	  // DEBUG:
	  //	  printf("SqlEngine.select: couldn't read the key in EQ!");
	  continue;
	}
		
	while( index.readForward(cursor, key, rid) == 0 ){
	  	to_print.push_back(rid);
	}

	

      case SelCond::LT:
	// locate the tuple and then print everything 
	// locate the key-tuple and then print everything until then but using readforward
	search_status = index.locate(compare_int_value, cursor);
	read_status = index.readForward(cursor, key, rid);
	
	
	smallestKey = index.getSmallestKey();
	smallest_search_status = index.locate(smallestKey, startCursor);
	

	// If we couldn't find a match just keep going and forget about this condition
	if (search_status != 0 || read_status != 0 || smallest_search_status != 0)
	  continue;
	
	// DEBUG
	//	printf("select: key = %d\n",key);
	//	printf("select: smallest key = %d\n",smallestKey);


	// Now keep reading forward until we get to the ending cursor's value
	while( index.readForward(startCursor, smallestKey, rid) == 0 && smallestKey < key) {
	  
	  
	  to_print.push_back(rid);
	  	  	  
	}
	
	break;


	// These guys are just the same deal but with the equal condition.
      case SelCond::GE:
	// locate the key-tuple and then print everything until then but using readforward
	search_status = index.locate(compare_int_value, cursor);
	
	// If we couldn't find a match just keep going and forget about this condition
	if (search_status != 0)
	  continue;
	

	// NOTE: Beacuse this is GREATER THAN, we will NOT skip the equal record
	// Now read the record id, and add the tuple to the to_print vector
	
	/*read_status = index.readForward(cursor, key, rid);
	
	if(read_status != 0) {
	  // DEBUG:
	  //	  printf("SqlEngine.select: couldn't read the key in EQ!");
	  continue;
	  }*/
		
	while( index.readForward(cursor, key, rid) == 0 ){
	  	to_print.push_back(rid);
	}
	break;


      case SelCond::LE:
	// locate the tuple and then print everything 
	// locate the key-tuple and then print everything until then but using readforward
	search_status = index.locate(compare_int_value, cursor);
	read_status = index.readForward(cursor, key, rid);
	
	
	smallestKey = index.getSmallestKey();
	smallest_search_status = index.locate(smallestKey, startCursor);
	

	// If we couldn't find a match just keep going and forget about this condition
	if (search_status != 0 || read_status != 0 || smallest_search_status != 0)
	  continue;
	
	// DEBUG
	//	printf("select: key = %d\n",key);
	//	printf("select: smallest key = %d\n",smallestKey);


	// Now keep reading forward until we get to the ending cursor's value
	while( index.readForward(startCursor, smallestKey, rid) == 0 && smallestKey <= key) {
	  
	  
	  to_print.push_back(rid);
	  	  	  
	}


	break;
      }
    }
    
    // DEBUG
    // printf("select: Size of to_print = %d\n",(int) to_print.size());

    // Go through the filtered list and print out the recordid's
    for (unsigned i = 0; i < to_print.size(); i++) {
      
      RecordId rid = to_print[i];
      
      // Open up this record and print it
      int key;
      string value;
  
      read_status = rf.read(rid, key, value);
      
      // if we can't read then just skip it
      if (read_status != 0)
	continue;
      
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
      
      
      
      
    }
    
    
    
    
    //TODO: figure out the count
    // TODO: print out the tuple
  }
  
  // print matching tuple count if "select count(*)"
  if (attr == 4) {
    fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  exit_select:
  rf.close();
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
