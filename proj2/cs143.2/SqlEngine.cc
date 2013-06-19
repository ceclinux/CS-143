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
  BTreeIndex idx;	// index for the table

  bool hasNotEquals = false;
  
  RC     rc;
  int    key;     
  string value;
  int    count = 0;
  int    diff;
  int	 idxState = 0;

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }
  
  SelCond* keyIs = NULL;
  SelCond* keyMax = NULL;
  SelCond* keyMin = NULL;
  SelCond* keyNe = NULL;
  
  SelCond* valIs = NULL;
  SelCond* valMax = NULL;
  SelCond* valMin = NULL;
  SelCond* valNe = NULL;
  
  int temp;
  
  int keyGe = 0;
  int keyLe = 0;
  
  for (unsigned i = 0; i < cond.size(); i++) {
	temp = atoi(cond[i].value);
	switch(cond[i].attr) {
	case 1:		// Key
		switch (cond[i].comp) {
		case SelCond::EQ:
			keyIs = (SelCond*) &cond[i];
			break;
		case SelCond::NE:
			hasNotEquals = true;
			keyNe = (SelCond*) &cond[i];
			break;
		case SelCond::LE:
			temp++;
		case SelCond::LT:
			if (keyMax == NULL || atoi(keyMax->value) < temp) {
				keyMax = (SelCond*) &cond[i];
				if(keyMax->comp == SelCond::LE) {
					keyLe = 1;
				} else {
					keyLe = 0;
				}
			}
			break;
		case SelCond::GE:
			temp--;
		case SelCond::GT:
			if (keyMin == NULL || atoi(keyMin->value) > temp) {
				keyMin = (SelCond*) &cond[i];
				if(keyMin->comp == SelCond::GE) {
					keyGe = 1;
				} else {
					keyGe = 0;
				}
			}
			break;
		}
		break;
	case 2:		// Value
		switch (cond[i].comp) {
		case SelCond::EQ:
			valIs = (SelCond*) &cond[i];
			break;
		case SelCond::NE:
			hasNotEquals = true;
			valNe = (SelCond*) &cond[i];
			break;
		case SelCond::LE:
			temp++;
		case SelCond::LT:
			if (valMax == NULL || atoi(valMax->value) < temp)
				valMax = (SelCond*) &cond[i];
			break;
		case SelCond::GE:
			temp--;
		case SelCond::GT:
			if (valMin == NULL || atoi(valMin->value) > temp)
				valMin = (SelCond*) &cond[i];
			break;
		}
		break;
	}
}
  
  if ((idx.open(table + ".idx", 'r')) == 0) {
	idxState = 1;
	
	IndexCursor ic;
	
	// Use the index lookup
	if (keyIs || keyMin || keyMax || keyNe) {
		/*
		* The idea here is to look up the minimum key value needed via the index and then iterate from there
		* Once I have those values I'll parse the rest of the constraints and iterate through the returned tuples checking those
		* So, if the query calls for a = condition, keyToFind is automatically set to that (since they're all ANDs, = overrides >
		* If there's a > or >=, I'll iterate from there
		* Unfortunately I still don't know if I'm reading from the index correctly
		* However, it looks like the conditions are being checked correctly
		*/
		int keyToFind;
		int keyLow;
		int keyHigh;
		int keyNot;
		if (keyIs)
			keyToFind = atoi(keyIs->value);
		else if(keyMin) {
			keyLow = atoi(keyMin->value);
			keyToFind = keyLow;
		}
		
		if(keyMax) {
			keyHigh = atoi(keyMax->value);
			if(! keyMin) {
				keyToFind = keyHigh;
			}
		}
		
		if(keyNe) {
			keyNot = atoi(keyNe->value);
		}

		// Location error
		if(keyMax) {
			if((rc = idx.locate(0, ic)) != 0) {
				idx.close();
				return rc;
			}
		} else if(! keyNe){
			if ((rc = idx.locate(keyToFind, ic)) != 0) {
				idx.close();
				return rc;
			}
		}
		
		// We read forward
		// If we have a keyIs, we read while the key is equal
		// If we have a keyMin, we read until the end (I think that works like this)
		
		while (idx.readForward(ic, key, rid) == 0 ) {		   			
			if(keyIs && key != keyToFind) continue;
			if(keyMin && ((keyGe==1)?(key < keyLow):(key <= keyLow))) continue;
			if(keyMax && ((keyLe==1)?(key > keyHigh):(key >= keyHigh))) break;
			if(keyNe && key == keyNot) continue;
			
			 if ((rc = rf.read(rid, key, value)) != 0) {
				fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
				idx.close();
				return rc;
			}

		    // check the conditions on the tuple
			if (valIs != NULL && value != valIs->value)		continue;
			if (valMax != NULL &&  value >= valMax->value)	continue;
			if (valMin != NULL && value <= valMin->value)	continue;
			if (valNe && value <= valNe->value) continue;

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
			
			if(keyIs && key == keyToFind) break;
			if(keyNe && key != keyNot) break;
		}
	}
	
	if(keyIs || keyMin || keyMax || keyNe) {
		if(attr == 4) {
			fprintf(stdout, "%d\n", count);
		}
	}
	
	if(! (keyIs || keyMin || keyMax)) {
		idx.close();
		goto tablescan;
	}
	
	idx.close();
  }
  
  //end B+ tree index code
  if(! idxState) {
	// scan the table file from the beginning
tablescan:
	rid.pid = rid.sid = 0;
	count = 0;
	while (rid < rf.endRid()) {
		// read the tuple
		if ((rc = rf.read(rid, key, value)) < 0) {
		  fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
		  goto exit_select;
		}

		// check the conditions on the tuple
		if (keyIs != NULL && (int) key != atoi(keyIs->value))	goto next_tuple;
		if (keyMax != NULL && (int) key >= atoi(keyMax->value))	goto next_tuple;
		if (keyMin != NULL && (int) key <= atoi(keyMin->value))	goto next_tuple;
		if (keyNe && (int) key == atoi(keyNe->value)) goto next_tuple;

		if (valIs != NULL && value != valIs->value)		goto next_tuple;
		if (valMax != NULL &&  value >= valMax->value)	goto next_tuple;
		if (valMin != NULL && value <= valMin->value)	goto next_tuple;
		if (valNe && value == valNe->value) goto next_tuple;

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
	}
	return rc;
}

RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
	/* your code here */
	// Open or create the new RecordFile
	RecordFile* r = new RecordFile(table + ".tbl", 'w');
	BTreeIndex idx;

	if(index) {
	//create the index file
		//cout << "Opening the index file." << endl;
		idx.open(table + ".idx", 'w');
	}

	// Open the input file
	ifstream ifs;
	ifs.open(loadfile.c_str());
	  
	string buf;
	while (ifs.good() && getline(ifs, buf, '\n')) {
		int key;
	    string s;
		RecordId id;
		//cout << "Buf is: " << buf << endl;

		// Parse the line of input
		if (parseLoadLine(buf, key, s) == 0) {
		    // Write the key-value pair to the RecordFile
			//cout << "Buf: " << buf << "\nKey: " << key << endl;
				
			if (r->append(key, s, id) == 0) {
			//insert into the B+ tree
				if(index) {
					if( idx.insert(key, id) != 0) {
						cout << "File write failed, exiting." << endl;
						return RC_FILE_WRITE_FAILED;
					}
				}
			} else {
				// ERROR
				return RC_INVALID_ATTRIBUTE;
			}
		 } else {
	    	// ERROR
			return RC_INVALID_ATTRIBUTE;
		}
	}
 
	idx.close();
	r->close();
	delete(r);
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
    if (s == NULL) {
		cout << "s == NULL" << endl;
		return RC_INVALID_FILE_FORMAT; 
	}

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
