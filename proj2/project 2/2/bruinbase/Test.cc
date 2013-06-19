/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "Bruinbase.h"

#include "PageFile.h"
#include "BTreeNode.h"
#include "BTreeIndex.h"

#include <string>
#include <assert.h>

using namespace std;


// int MAX_NONLEAFDATA = BTNonLeafNode::NL_MAX_KEYS;

void checkStatus(string msg, RC status) {
  
  if(status != 0){
    fprintf(stderr, "%s  | status = %d\n", msg.c_str(), status);
    //  exit(-1);
  }

}


RC BTNonLeafNode_insert_size_check(BTNonLeafNode& nl) {
   // Add some stuff to the empty BTNonLeafNode
  nl.insert(25, 30);
  nl.insert(35, 40);
  
  if(nl.getKeyCount() != 3)
    return -1;
  else return 0;

}


RC BTNonLeafNode_insert_max_check(BTNonLeafNode& nl) {
  
  // DEBUG
  printf("maxnonleafdata = %d\n", MAX_NONLEAFDATA);


  // Keep adding stuff until we get to the max
  for(int i = 0; i <  MAX_NONLEAFDATA * 2; i++){
    
    printf("Inserting key = %d\n", i);
    RC status = nl.insert(i, i);

    

    
    if(status != 0) 
      return status;
  }

  // fprintf(stdout, "Size of nl now is = %d", nl.getKeyCount());
  return 0;
}

RC BTNonLeafNode_search_test(BTNonLeafNode& nl) {
  // By now we should have the keys 10 [23] 20 [25] 30 [35] 40
  
  PageId pid;
  
  
  // Look for the key in between
  RC search_status = nl.locateChildPtr(24, pid);
  checkStatus("Error in searching for 24!", search_status);
  assert(pid == 20);
  

  // Look for the key on match
  search_status = nl.locateChildPtr(23, pid);
  checkStatus("Error in searching for 23!", search_status);
  assert(pid == 20);
  
  
  // Look for the key on the first
  search_status = nl.locateChildPtr(1, pid);
  checkStatus("Error in searching for 1!", search_status);
  assert(pid == 10);
  

  // Look for the key on the last
  search_status = nl.locateChildPtr(38, pid);
  checkStatus("Error in searching for 38!", search_status);
  assert(pid == 40);
  
  
  fprintf(stdout, "search for 38: pid = %d", pid);
  //  fprintf(stdout, "BTNonLeafNode Search Test Passed!");
  

}

void BTNonLeafNode_test(){
  // Open up a new pagefile
  PageFile pf;
  
  RC open_status = pf.open("testing.pf", 'w');
  checkStatus("Error in opening testing.pf in write mode!", open_status);
  
  // Create and init an empty BTNonLeafNode
  BTNonLeafNode nl;
 
  
  RC root_status = nl.initializeRoot(10, -1, -1);
  checkStatus("Error in initializing root!", root_status);
  
  //RC insert_status = BTNonLeafNode_insert_size_check(nl);
  //checkStatus("Error in inserting to BTNonLeafNode!", insert_status);
  

  // Use a new page file
  PageFile pf2;
  open_status = pf2.open("testing2.pf", 'w');
  checkStatus("Error in opening testing.pf in write mode!", open_status);
  
  // Read the contents of pf2
  //nl.read(1, pf2);

  RC insert_max_status = BTNonLeafNode_insert_max_check(nl);
  checkStatus("Error in inserting max nodes to BTNonLeafNode!", insert_max_status);
  

  // // Now write to the page file
  // nl.write(1, pf2);

  fprintf(stdout, "Size of BTNonLeafNode = %d\n", nl.getKeyCount());
  
  

  // // Search test
  // // BTNonLeafNode_search_test(nl);
  
  
  // // Write the empty leaf node to the page file.
  
  // nl.write(pf.endPid(),pf);


  // fprintf(stdout, "Writing to pid = %d", pf.endPid());

  // Open up the NonLeafNode from the page file
  
  // Go through its contents.
  


}



void BTreeIndex_test() {
  
  BTreeIndex index;
  
  index.open("tree.index", 'w');
  index.readMetadata();
  

  int numRecords = 12000;

  // Go from [10 - 50]
  for (int i = 1; i < numRecords; i++) 
    {
      RecordId insertMe;
      insertMe.pid = i;
      insertMe.sid = i;
      
      int key_to_insert = i;
      
      RC insert_status = index.insert(key_to_insert, insertMe);
      checkStatus("Error in insert of BTreeIndex!", insert_status);
     

      printf("Inserting %d with pid = %d, sid = %d\n", key_to_insert, insertMe.pid, insertMe.sid);
    }
  
  
  // Now try to find all the records
  for (int j = 1; j < numRecords; j++) 
    {
      IndexCursor cursor;
      cursor.pid = -1;
      cursor.eid = -1;
      int key_to_search = j;
      RC search_status = index.locate(key_to_search, cursor);

      fprintf(stderr, "Checking key = %d\n.", j);
      checkStatus("Error in locate of BTreeIndex!", search_status);
      
      int key;
      RecordId rid;
      RC read_status = index.readForward(cursor, key, rid);
      //      checkStatus("Error in readForward of BTreeIndex!", read_status);
      
      printf("Search for j = %d returned pid = %d, sid = %d.\n", key_to_search, rid.pid, rid.sid);
      
    }
  

}




RC BTNonLeafNode_insertAndSplit_check() {
  
  BTNonLeafNode nl;
  
  nl.initializeRoot(10,-1,-1);
  // DEBUG
  printf("maxnonleafdata = %d\n", MAX_NONLEAFDATA);

  // Keep adding stuff until we get to the max
  for(int i = 0; i <  MAX_NONLEAFDATA; i++){
    
    printf("Inserting key = %d\n", i);
    RC status = nl.insert(i, i);
    
    if(status != 0) 
      return status;
  }

  
  BTNonLeafNode sibling;
  int midKey;

  // Now split this node
  nl.insertAndSplit(10, 128, sibling, midKey);
  
  fprintf(stdout, "Size of nl now is = %d\n", nl.getKeyCount());
  fprintf(stdout, "Size of sibling now is = %d\n", sibling.getKeyCount());
  
  fprintf(stdout, "Midkey is = %d\n",midKey);
    
  return 0;
}



int main()
{
  
  //BTNonLeafNode_test();
  BTreeIndex_test();
  

  // BTNonLeafNode_insertAndSplit_check();
 
  return 0;
}
