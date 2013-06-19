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

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }

  //Make use of With Indexing Option
  BTreeIndex treeIndex;
  int compareMe = 0;
  //1 is key column
  SelCond* NotEqual1 = NULL;
  SelCond* Max1 = NULL;
  SelCond* Equal1 = NULL;
  SelCond* Min1 = NULL;
  //2 is value column
  SelCond* NotEqual2 = NULL;
  SelCond* Max2 = NULL;
  SelCond* Equal2 = NULL;
  SelCond* Min2 = NULL;

  for(int i=0; i < cond.size(); i++)
  {
	  //Can also use stringstream too
	  compareMe = atoi(cond[i].value);
	  //Iterate through all the members of the vector input
	  if(cond[i].attr == 1)
	  {
		  //1 is Key Column
		  if(cond[i].comp == SelCond::NE)
		  {
			  //Not Equal
			  NotEqual1 = (SelCond*) &cond[i];
		  }
		  else if(cond[i].comp == SelCond::LE)
		  {
			  //Less Than or Equal To
			  compareMe++;
			  int bufferVal = atoi(Max1->value);
			  if(Max1 == NULL || bufferVal < compareMe)
			  {
				  //Set new pointer
				  Max1 = (SelCond*) &cond[i];
			  }
		  }
		  else if(cond[i].comp == SelCond::LT)
		  {
			  // Less THan
			  int bufferVal = atoi(Max1->value);
			  if(Max1 == NULL || bufferVal < compareMe)
			  {
				  //Set new pointer
				  Max1 = (SelCond*) &cond[i];
			  }
		  }
		  else if(cond[i].comp == SelCond::EQ)
		  {
			  //Equal
			  Equal1 = (SelCond*) &cond[i];
		  }
		  else if(cond[i].comp == SelCond::GE)
		  {
			  //Greater or Equal To
			  compareMe--;
			  int bufferVal = atoi(Min1->value);
			  if(Min1 == NULL || bufferVal > compareMe)
			  {
				  //Set new pointer
				  Min1 = (SelCond*) &cond[i];
			  }
		  }
		  else if(cond[i].comp == SelCond::GT)
		  {
			  int bufferVal = atoi(Min1->value);
			  if(Min1 == NULL || bufferVal > compareMe)
			  {
				  //Set new pointer
				  Min1 = (SelCond*) &cond[i];
			  }
		  }
		  else
			  cerr << "Error: SqlEngine.cc. SHould never reach here" << endl;
	  }
	  else if(cond[i].attr == 2)
	  {
		  //We have a value column here
		  // we do the same things again ...
		  if(cond[i].comp == SelCond::NE)
		  {
			  //Not Equal
			  NotEqual2 = (SelCond*) &cond[i];
		  }
		  else if(cond[i].comp == SelCond::LE)
		  {
			  //Less Than or Equal To
			  compareMe++;
			  int bufferVal = atoi(Max2->value);
			  if(Max2 == NULL || bufferVal < compareMe)
			  {
				  //Set new pointer
				  Max2 = (SelCond*) &cond[i];
			  }
		  }
		  else if(cond[i].comp == SelCond::LT)
		  {
			  // Less THan
			  int bufferVal = atoi(Max2->value);
			  if(Max2 == NULL || bufferVal < compareMe)
			  {
				  //Set new pointer
				  Max2 = (SelCond*) &cond[i];
			  }
		  }
		  else if(cond[i].comp == SelCond::EQ)
		  {
			  //Equal
			  Equal2 = (SelCond*) &cond[i];
		  }
		  else if(cond[i].comp == SelCond::GE)
		  {
			  //Greater or Equal To
			  compareMe--;
			  int bufferVal = atoi(Min2->value);
			  if(Min2 == NULL || bufferVal > compareMe)
			  {
				  //Set new pointer
				  Min2 = (SelCond*) &cond[i];
			  }
		  }
		  else if(cond[i].comp == SelCond::GT)
		  {
			  int bufferVal = atoi(Min2->value);
			  if(Min2 == NULL || bufferVal > compareMe)
			  {
				  //Set new pointer
				  Min2 = (SelCond*) &cond[i];
			  }
		  }
		  else
			  cerr << "Error: SqlEngine.cc. SHould never reach here" << endl;
	  }
	  else
		  cerr << "Error: SqlEngine.cc. Should Never reach here" << endl;
  }
  int indexFlag = 0;
  if(treeIndex.open(table+".idx",'r')==0)
  {
	//Success
	  IndexCursor cursor;
	  indexFlag = 1;
	  
	  //Lookup
	  if(Equal1 || Min1 || Max1 || NotEqual1)
	  {
		  int target = 0;
		  int lowerBound = 0;
		  int upperBound = 0;
		  int notkey = 0;
		  //Key Value
		  if(Equal1)
			  target = atoi(Equal1->value);
		  if(Min1)
		  {
			  lowerBound = atoi(Min1->value);
			  target = lowerBound;
		  }
		  if(Max1)
		  {
			  upperBound = atoi(Max1->value);
			  if(Min1 == NULL)
			  {
				  target = upperBound;
			  }
		  }
		  if(NotEqual1)
		  {
			  notkey = atoi(NotEqual1->value);
		  }

		  //Error Checking
		  if(Max1 && treeIndex.locate(0,cursor)!=0)
		  {
			  int returnvalue = treeIndex.locate(0,cursor);
			  treeIndex.close();
			  return returnvalue;
		  }
		  if(NotEqual1 == NULL && treeIndex.locate(target, cursor) != 0)
		  {
			   int returnvalue = treeIndex.locate(target, cursor);
			   treeIndex.close();
			   return returnvalue;
		  }

		  //Read Forward Now
		  while(treeIndex.readForward(cursor,key,rid) == 0) //Sucessful
		  {
			  if(Max1 && Max1->comp == SelCond::LE)
			  {
				  if(target > upperBound)
					  break;
			  }
			  else if(Max1 && Max1->comp != SelCond::LE)
			  {
				  if(target >= upperBound)
					  break;
			  }

			  if(rf.read(rid,key,value) != 0)
			  {
				  //Error
				  int returnvalue = rf.read(rid,key,value);
				  treeIndex.close();
				  return returnvalue;
			  }

			  count++;
			  if(attr == 1) //Key
				  cout << key << endl;
			  else if(attr==2) //Value
				  cout << value.c_str() << endl;
			  else if(attr==3) //All
				  cout << key << " " << value.c_str() << endl;
			 
			  if(Equal1 && key == target)
				  //Found it
				  break;
			  if(NotEqual1 && key != notkey)
				  //Still works
				  break;
		  }
		  if(Equal1 == NULL || Min1 == NULL || Max1 == NULL) {
			  treeIndex.close();
			  indexFlag = 0;
		  }
		  else
			treeIndex.close();
	  }
  }
  if(indexFlag == 0)
  {
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
		int equal1val = atoi(Equal1->value);
		int max1val = atoi(Max1->value);
		int min1val = atoi(Min1->value);
		int notequalval = atoi(NotEqual1->value);
		if (Equal1 != NULL && (int) key != equal1val)	goto next_tuple;
		if (Max1 != NULL && (int) key >= max1val)	goto next_tuple;
		if (Min1 != NULL && (int) key <= min1val)	goto next_tuple;
		if (NotEqual1 && (int) key == notequalval) goto next_tuple;

		if (NotEqual2 != NULL && value != Equal2->value)		goto next_tuple;
		if (Max2 != NULL &&  value >= Max2->value)	goto next_tuple;
		if (Min2 != NULL && value <= Min2->value)	goto next_tuple;
		if (NotEqual2 && value == NotEqual2->value) goto next_tuple;

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
}

// return 0 if successful
RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  	RecordFile rf;
    RC rc = rf.open(table + ".tbl", 'w');
    if (rc != 0){
        rf.close();
        return rc;
    }
    
    ifstream input; // input stream (loadfile)
	input.open (loadfile.c_str()); // load file
	
	// if couldn't open the file, error
	if (input.fail()){
		input.close();
		return RC_FILE_OPEN_FAILED;
	}
    
	BTreeIndex treeIndex;
	if(index)
	{
		rc = treeIndex.open(table + ".idx", 'w');
        
        if (!rc){// if failed
            input.close();
            rf.close();
            return rc;
        }
	}

	string line; // read each line
	printf("0> ");
	// if we were able to open the input stream
	if (input.is_open()){
		while (input.good() && getline(input, line, '\n')){
			RecordId rid; 		// rid
			string val;			// value of each line
			int key;			// key of each line
			
			if (!parseLoadLine(line, key, val)){
				printf("1> ");
				if (!rf.append(key, val, rid)){
                    printf("2> ");
					// we successfully appended
					if(index)
					{
                        fprintf(stdout, "3> ");
						if(treeIndex.insert(key,rid) != 0)
						{
							cerr << "Unable to insert <" << key << "> to the treeIndex" << endl;
							return RC_FILE_WRITE_FAILED;
						}
					}
				} else {
					return RC_INVALID_ATTRIBUTE; // error in appending
				}
			} else {
				return RC_INVALID_ATTRIBUTE; // there's an error in parsing
			}
		}
	} 
	
	treeIndex.close();
	input.close();	// close the input stream
	rf.close(); 	// close the recordfile
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
