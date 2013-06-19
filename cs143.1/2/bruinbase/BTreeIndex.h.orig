/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#ifndef BTREEINDEX_H
#define BTREEINDEX_H

#include "Bruinbase.h"
#include "PageFile.h"
#include "RecordFile.h"
             
/**
 * The data structure to point to a particular entry at a b+tree leaf node.
 * An IndexCursor consists of pid (PageId of the leaf node) and 
 * eid (the location of the index entry inside the node).
 * IndexCursor is used for index lookup and traversal.
 */
typedef struct {
  // PageId of the index entry
  PageId  pid;  
  // The entry number inside the node
  int     eid;  
} IndexCursor;

/**
 * Implements a B-Tree index for bruinbase.
 * 
 */
class BTreeIndex {
 public:
  static const int METADATA_PAGE = 0;
  static const int ROOT_PID_LOC = 0;
  static const int HEIGHT_LOC = 4;
  static const int SM_KEY_LOC = 8;

  BTreeIndex();

  /**
   * Open the index file in read or write mode.
   * Under 'w' mode, the index file should be created if it does not exist.
   * @param indexname[IN] the name of the index file
   * @param mode[IN] 'r' for read, 'w' for write
   * @return error code. 0 if no error
   */
  RC open(const std::string& indexname, char mode);

  /**
   * Close the index file.
   * @return error code. 0 if no error
   */
  RC close();
    
  /**
   * Insert (key, RecordId) pair to the index.
   * @param key[IN] the key for the value inserted into the index
   * @param rid[IN] the RecordId for the record being inserted into the index
   * @return error code. 0 if no error
   */
  RC insert(int key, const RecordId& rid);

  /**
   * Find the leaf-node index entry whose key value is larger than or
   * equal to searchKey and output its location (i.e., the page id of the node
   * and the entry number in the node) as "IndexCursor."
   * IndexCursor consists of pid (page id of the node that contains the 
   * searchKey) and eid (the entry number inside the node)
   * to indicate the location of a particular index entry in the B+tree.
   * Note that, for range queries, we need to scan the B+tree leaf nodes.
   * For example, if the query is "key > 1000", we should scan the leaf
   * nodes starting with the key value 1000. For this reason,
   * this function returns the location of the leaf node entry
   * for a given searchKey, instead of returning the RecordId
   * associated with the searchKey.
   * Using the returned "IndexCursor", you will have to call readForward()
   * to retrieve the actual (key, rid) pair from the index.
   * @param key[IN] the key to find
   * @param cursor[OUT] the cursor pointing to the first index entry
   * with the key value
   * @return error code. 0 if no error.
   */
  RC locate(int searchKey, IndexCursor& cursor);

  /**
   * Read the (key, rid) pair at the location specified by the index cursor,
   * and move foward the cursor to the next entry.
   * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
   * @param key[OUT] the key stored at the index cursor location
   * @param rid[OUT] the RecordId stored at the index cursor location
   * @return error code. 0 if no error
   */
  RC readForward(IndexCursor& cursor, int& key, RecordId& rid);

  /**
   * Read the metadata (rootPid, treeHeight) from the page file.
   * @return error code. 0 if no error.
   */
  RC readMetadata(); 

  /**
   * Write the metadata (rootPid, treeHeight) to the page file.
   * @return error code. 0 if no error. 
   */
  RC writeMetadata(); 

  /**
   * Recursive helper function for insert.
   * @param key[IN] the key for the value inserted into the index
   * @param rid[IN] the RecordId for the record being inserted into the index
   * @param currPid[IN] the current pid to read
   * @param level[IN] the current level in the tree
   * @param retPid[OUT] the pid of the sibling node created if there was a split
   * @param retKey[OUT] the key of the sibling node created if there was a split
   * @param splitStatus[OUT] returns true if there was a split, false if not.
   * @return error code. 0 if no error.
   */ 
  RC insertHelper(int key, const RecordId& rid, PageId currPid, int level, PageId& retPid, int& retKey); //, bool& splitStatus);

  /**
   * Helper method to create a new root.
   * @param key[IN] the key to place in the root
   * @param leftPid[IN] page with values < key
   * @param rightPid[IN] page with values >= key
   * @return error code. 0 if no error.
   */
  RC newRoot(int key, PageId leftPid, PageId rightPid);

  int getSmallestKey();


 private:
  PageFile pf;         /// the PageFile used to store the actual b+tree in disk
 
  PageId   rootPid;    /// the PageId of the root node
  int      treeHeight; /// the height of the tree
  int      smallestKey;/// the smallest key in the tree
  /// Note that the content of the above two variables will be gone when
  /// this class is destructed. Make sure to store the values of the two 
  /// variables in disk, so that they can be reconstructed when the index
  /// is opened again later.
};

#endif /* BTREEINDEX_H */
