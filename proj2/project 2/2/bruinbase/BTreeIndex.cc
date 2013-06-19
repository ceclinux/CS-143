/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "BTreeIndex.h"
#include "BTreeNode.h"

using namespace std;

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
    rootPid = -1;
    treeHeight = 0;
    smallestKey = -1;
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */
RC BTreeIndex::open(const string& indexname, char mode)
{
    RC rc = pf.open(indexname, mode);

    return rc;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{ 
    RC rc = pf.close();

    return rc;
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{
    // Return value
    RC rc;
    
    // Special case: Empty tree
    if (treeHeight == 0)
    {
        // Create an empty node
        BTLeafNode ln;
        ln.initialize();
        
        // Insert the entry
        ln.insert(key, rid);
        
        // Figure out where to write to
        // Page 0 is for metadata
        rootPid = pf.endPid();
        if (rootPid == BTreeIndex::METADATA_PAGE)
        {
            rootPid++;
        }
        
        // Write the entry to the page file
        rc = ln.write(rootPid, pf);
        
        treeHeight = 1;
    } else
    {
        PageId retPid;
        int retKey;
        rc = insertHelper(key, rid, rootPid, 1, retPid, retKey);
        
        // DEBUG
        //	if(treeHeight > 2)
        //	  printf("HEIGHT IS 3 NOW!\n");
        
        
    }
    
    if (smallestKey == -1 || key < smallestKey)
    {
        smallestKey = key;
    }
    
    return rc;
}



RC BTreeIndex::insertHelper(int key, const RecordId& rid, PageId currPid, int level, PageId& retPid, int& retKey)
{
    // BASE CASE: Leaf node
    if (level == treeHeight)
    {
        // Read the node information
        BTLeafNode ln;
        ln.initialize();
        ln.read(currPid, pf); 

        // Attempt to insert
        RC rc = ln.insert(key, rid);
      
        // Successful insert (node not full)
        if (rc == 0)
        {
            // Write to page
            ln.write(currPid, pf);

            // DEBUG 
            //printf("BTreeIndex.insert: Inserted key = %d at pid = %d. TreeHeight = %d. rc = %d.\n", key, currPid, treeHeight, rc);
	  
            return rc;
        } else // Node full
        {
            // Create a sibling node
            BTLeafNode sn;
            sn.initialize();
	  
            // Return key from insertAndSplit            
            int sibKey;
	  
            // Insert and split
            ln.insertAndSplit(key, rid, sn, sibKey);
	  
            // Write the sibling node
            PageId sibPid = pf.endPid();
            sn.write(sibPid, pf);	  

            // Set the leaf node's pointer
            ln.setNextNodePtr(sibPid);
         
            // Write the leaf node
            ln.write(currPid, pf);

            // Set the return information
	  
            retPid = sibPid;
            retKey = sibKey;
	  
            // If this was a root, we now have to create a new root
            if (level == 1)
            {
                rc = this->newRoot(sibKey, currPid, sibPid);                    // DEBUG 
//                    printf("INFO: Created a new root. Current Height = %d.\n", treeHeight);
            }     
	    	  
            return rc;
        } // End else: Node full (leaf node)
    } else // RECURSIVE CASE: Non leaf node
    {
        // Initialize and read
        BTNonLeafNode n;
        n.initializeRoot(-1, -1, -1);
        n.read(currPid, pf);
        
        PageId nextPid, splitPid;
        int splitKey;
        
        n.locateChildPtr(key, nextPid);
        
        // RECURSIVE ITERATION
        RC rc = this->insertHelper(key, rid, nextPid, level+1, splitPid, splitKey);
        
        // This means we had to split the node below
        if (rc == RC_NODE_FULL)
        {
            // Attempt to insert using the return information
            rc = n.insert(splitKey, splitPid);
            
            // Success
            if (rc == 0)
            {
                // Write the non leaf node and we're done
                n.write(currPid, pf);
                return rc;
            } else // Full node
            {
                RC rc = RC_NODE_FULL;
                // Create sibling and initialize
                BTNonLeafNode sn;
                sn.initializeRoot(-1, -1, -1);
                
                // InsertAndSplit
                int midKey;
                n.insertAndSplit(splitKey, splitPid, sn, midKey);
                
                // Write the sibling to endPid
                PageId sibId = pf.endPid();
                sn.write(sibId, pf);		
                
                // Write the node that was split
                n.write(currPid, pf);
                
                // If this is a root node
                if (level == 1)
                {
                    rc = this->newRoot(midKey, currPid, sibId);
                    
                    // DEBUG 
//                    printf("INFO: Created a new root. Current Height = %d.\n", treeHeight);
                }
                
                // Set return info
                retPid = sibId;
                retKey = midKey;
                
                return rc;
            } // End else: Full node, non leaf node
        } else // Inserted properly
        {
            // Previous bug, rc was not being returned here.
            return rc;
        }
    } // End else: Recursive case
}




RC BTreeIndex::newRoot(int key, PageId leftPid, PageId rightPid)
{
    // Initialize a new root node
    BTNonLeafNode n;
    RC rc = n.initializeRoot(leftPid, key, rightPid);
 
    // Set the new root pid
    rootPid = pf.endPid();

    // Write the new root
    n.write(rootPid, pf);

    // Set the new tree height
    treeHeight++;

    return rc;
}

/*
 * Find the leaf-node index entry whose key value is larger than or 
 * equal to searchKey, and output the location of the entry in IndexCursor.
 * IndexCursor is a "pointer" to a B+tree leaf-node entry consisting of
 * the PageId of the node and the SlotID of the index entry.
 * Note that, for range queries, we need to scan the B+tree leaf nodes.
 * For example, if the query is "key > 1000", we should scan the leaf
 * nodes starting with the key value 1000. For this reason,
 * it is better to return the location of the leaf node entry 
 * for a given searchKey, instead of returning the RecordId
 * associated with the searchKey directly.
 * Once the location of the index entry is identified and returned 
 * from this function, you should call readForward() to retrieve the
 * actual (key, rid) pair from the index.
 * @param key[IN] the key to find.
 * @param cursor[OUT] the cursor pointing to the first index entry
 *                    with the key value.
 * @return error code. 0 if no error.
 */
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
  BTNonLeafNode nl;
  nl.initializeRoot(0, -1, -1);
  PageId pid_to_read = rootPid;
  
  // Keep searching until we get to the leaf
  int currentHeight = 1;
  
  // DEBUG 
  //  printf("Current Tree Height = %d\n", treeHeight);
  
  
  while(currentHeight < treeHeight) {

    // DEBUG
    fprintf(stderr, "NON-LEAF: Reading pid = %d\n", pid_to_read);
    
    
    // DEBUG 
    //printf("Current Tree Height = %d\n", currentHeight);
  

    // Read the node
    RC non_leaf_read = nl.read(pid_to_read, pf);
    
    // Make sure everything was read correctly
    if(non_leaf_read != 0) {
      
      // DEBUG
      fprintf(stderr, "Error in NON Leaf-node read. pid = %d\n", pid_to_read);

      return non_leaf_read;
    }

    // Look for the next pid to follow, based off of the key to find.
    RC locate_status = nl.locateChildPtr(searchKey, pid_to_read);
    
    if(locate_status != 0){
      

      // DEBUG
      fprintf(stderr, "Couldn't find key = %d. pid = %d", searchKey, pid_to_read);

      return locate_status;
    }
    currentHeight++;
  }
  
  
  // DEBUG
  //printf("LEAF: Reading pid = %d\n", pid_to_read);

  // We should now reach the leaf nodes.
  BTLeafNode lf;
  lf.initialize();
  
  // Read the page file and make sure there are no errors.
  RC leaf_read_status = lf.read(pid_to_read, pf);
  
  if(leaf_read_status != 0) {
    
    // DEBUG
    fprintf(stdout, "Error in lf node read. pid = %d", pid_to_read);

    return leaf_read_status;
  }

  // Find the entry whose key value is larger than or equal to searchKey
  int located_eid;
  RC locate_status = lf.locate(searchKey, located_eid);

  // Check that we have no errors

  if(locate_status != 0) {

    // DEBUG
    printf("BTreeIndex.locate: Couldn't find %d in the leaf node of pid = %d\n", searchKey, pid_to_read);
     return locate_status;
  }
   
  
  cursor.pid = pid_to_read;
  cursor.eid = located_eid;
  
  // DEBUG
  //printf("Found @ pid = %d.\t", pid_to_read);
  
  return 0;
}

/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
  BTLeafNode lf;
  lf.initialize();
  
  // Read what is located the specified location by the cursor, then move
  // the cursor forward to the next entry.
  
  PageId pid_to_read = cursor.pid;
  int entry_to_read = cursor.eid;
  
  // Read the page into the Leaf Node
  RC read_status = lf.read(pid_to_read, pf);
  if(read_status != 0) 
    return read_status;
  
  // Find out what's at the current entry
  RC read_entry = lf.readEntry(entry_to_read, key, rid);
 
  if (read_entry != 0)
    return read_entry;
 
  // Move the eid up (or pid up, and eid back to zero, 
  // if we are at the end of the number of entries)
  entry_to_read++;
  
  // Check that eid is not at the last entry
  if(entry_to_read >= lf.getKeyCount()) {
    // If it is, then move up the pid and reset the entry
    entry_to_read = BTLeafNode::LEAF_FIRST_ENTRY;
    
    cursor.pid = lf.getNextNodePtr();
    cursor.eid = entry_to_read;
    return 0;
  }else{

    cursor.eid = entry_to_read;
    return 0;
  }
}

RC BTreeIndex::readMetadata()
{
    /* IMPORTANT:
     * The open function must be called prior to this function being called.
     * This function must be called prior to any insertions or lookups.
     */

    // The file is empty
    if (pf.endPid() == 0)
    {
        // Reset the rootPid and treeHeight just in case we're reusing this
        rootPid = -1;
        treeHeight = 0;
        smallestKey = -1;
        return 0;
    }

    // Buffer for reading
    char buf[PageFile::PAGE_SIZE];
    
    pf.read(BTreeIndex::METADATA_PAGE, buf);

    int r = BTreeIndex::ROOT_PID_LOC;
    int h = BTreeIndex::HEIGHT_LOC;
    int s = BTreeIndex::SM_KEY_LOC;

    char* b = buf;
    int* bint = (int*) b;
    rootPid = *(bint+r/sizeof(int));
    treeHeight = *(bint+h/sizeof(int));
    smallestKey = *(bint+s/sizeof(int));
}

RC BTreeIndex::writeMetadata()
{
    /* IMPORTANT:
     * The open function must be called prior to this function being called.
     * This function must be called prior to the close function being called.
     */

    char buffer[PageFile::PAGE_SIZE];

    int r = BTreeIndex::ROOT_PID_LOC;
    int h = BTreeIndex::HEIGHT_LOC;
    int s = BTreeIndex::SM_KEY_LOC;
    
    char* b = buffer;
    int* bint = (int*) b;
    *(bint+r/sizeof(int)) = rootPid;
    *(bint+h/sizeof(int)) = treeHeight;
    *(bint+s/sizeof(int)) = smallestKey;
    b = (char*) bint;

    RC rc = pf.write(BTreeIndex::METADATA_PAGE, buffer);
   
    return rc;
}

int BTreeIndex::getSmallestKey()
{
    return smallestKey;
}
