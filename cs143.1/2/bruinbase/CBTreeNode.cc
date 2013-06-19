#include "BTreeNode.h"

using namespace std;

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{ 
  // Use PageFile::read to read into buffer
  RC rc = pf.read(pid, buffer);
  return rc; 
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{
  // Use PageFile::write to write from the buffer
  RC rc = pf.write(pid, buffer);
  return rc; 
}
/*
 * An entry of all -1 means an empty entry.
 * The first entry is set to all -2 to signify that the node is a leaf node.
 * A -1 PageId in the last 4 bytes means it's the last leaf node.
 *
 * Current search algorithm is linear.
 *
 * BTreeIndex must:
 * - initialize each leaf node before inserting into it
 * - for insertAndSplit, assign the new pointer for the node being split
 */ 

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{ 
  int numKeys = 0;
  int limit = PageFile::PAGE_SIZE - sizeof(PageId);
  int key;
  RecordId rid;

  // Last 4 bytes are a PageId pointer to next leaf node
  for (int index = LEAF_FIRST_ENTRY * LEAF_ENTRY_SIZE; index < limit; index += LEAF_ENTRY_SIZE)
  {
    convertToLeafEntry(buffer, index, key, rid);

    // All -1's means empty entry
    if (rid.pid == -1 && rid.sid == -1 && key == -1)
    {
	break;
    }

    numKeys++;
  }

  return numKeys; 
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{
  int numKeys = this->getKeyCount();

  // Check if the node is already full  
  if (numKeys >= LEAF_MAX_ENTRIES)
  {
    return RC_NODE_FULL;
  }

  int eid;
  int oKey;
  RecordId oRid;

  // Find the spot to insert
  RC rc = locate(key, eid);

  // No key >= searchKey, so insert at end
  if (rc != 0)
  {
    eid = numKeys;
  }

  // Move all chars in the buffer by the size of one leaf entry
  for (int i = numKeys * LEAF_ENTRY_SIZE - 1; i >= eid * LEAF_ENTRY_SIZE; i--)
  {
    buffer[i+LEAF_ENTRY_SIZE] = buffer[i];
  }

  char buf[LEAF_ENTRY_SIZE];

  // Convert the (key, rid) pair to a char array
  convertToChar(key, rid, buf);
 
  // Insert the new entry
  for (int i = eid * LEAF_ENTRY_SIZE; i < eid * LEAF_ENTRY_SIZE + LEAF_ENTRY_SIZE; i++)
  {
    buffer[i] = buf[i - eid * LEAF_ENTRY_SIZE];
  }
 
  return 0;
}

/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid, 
                              BTLeafNode& sibling, int& siblingKey)
{ 
  // Make sure the node is full
  if (getKeyCount() < LEAF_MAX_ENTRIES)
  {
    return RC_INVALID_FILE_FORMAT;
  }

  // The entry id of the last entry before the split
  int spid = (int) (LEAF_MAX_ENTRIES / 2 + 1);
  int oKey;
  RecordId oRid;
  int eKey = -1;
  RecordId eRid = {-1, -1};

  for (int i = spid*LEAF_ENTRY_SIZE; i < LEAF_MAX_ENTRIES*LEAF_ENTRY_SIZE; i += LEAF_ENTRY_SIZE)
  {
    convertToLeafEntry(buffer, i, oKey, oRid);
    
    // Copy over the entries that will now be in sibling
    sibling.insert(oKey, oRid);

    // Assign the sibling key (will be changed if necessary)
    if (i == spid*LEAF_ENTRY_SIZE)
    {
	siblingKey = oKey;
    }

    char buf[LEAF_ENTRY_SIZE];
    convertToChar(eKey, eRid, buf);
     
    // Turn this entry in the current leaf to an empty entry
    for (int j = i; j < i + LEAF_ENTRY_SIZE; j++)
    {
	buffer[j] = buf[j-i];
    }
  }

  // Set the sibling's node pointer
  sibling.setNextNodePtr(getNextNodePtr()); 

  // Now insert the new record
  if (key < siblingKey)
  {
    insert(key, rid);
  } else
  {
    sibling.insert(key, rid);
  }

  return 0;
}

/*
 * Find the entry whose key value is larger than or equal to searchKey
 * and output the eid (entry number) whose key value >= searchKey.
 * Remeber that all keys inside a B+tree node should be kept sorted.
 * @param searchKey[IN] the key to search for
 * @param eid[OUT] the entry number that contains a key larger than or equalty to searchKey
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{ 
  int numKeys = getKeyCount();

  int oKey;
  RecordId oRid;

  for (int i = LEAF_FIRST_ENTRY; i < numKeys; i++)
  {
    convertToLeafEntry(buffer, i * LEAF_ENTRY_SIZE, oKey, oRid);

    if (oKey >= searchKey)
    {
	eid = i;
        return 0;
    }
  }
 
  return RC_NO_SUCH_RECORD;
}

/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int& key, RecordId& rid)
{ 
  if (eid < 0 || eid >= getKeyCount())
  {
    return RC_INVALID_CURSOR;
  }

  convertToLeafEntry(buffer, eid*LEAF_ENTRY_SIZE, key, rid);

  return 0;
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{ 
  return this->getNextNodePtrHelp(buffer);
}

PageId BTLeafNode::getNextNodePtrHelp(char* buffer)
{
  int* bint = (int*) buffer;
  return *(bint+255);
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{ 
  this->setNextNodePtrHelp(buffer, pid);

  return 0; 
}

void BTLeafNode::setNextNodePtrHelp(char* buffer, PageId pid)
{
  int* bint = (int*) buffer;
  *(bint+255) = pid;
  buffer = (char*) bint;
}

void BTLeafNode::convertToLeafEntry(char* buffer, int index, int& key, RecordId& rid)
{
  int* bint = (int*) buffer;
  rid.pid = *(bint+index/sizeof(int));
  rid.sid = *(bint+index/sizeof(int)+1);
  key = *(bint+index/sizeof(int)+2);
}

void BTLeafNode::convertToChar(int key, RecordId rid, char* buf)
{
  int* bint = (int*) buf;
  *(bint) = rid.pid;
  *(bint+1) = rid.sid;
  *(bint+2) = key;
  buf = (char*) bint;    
}

void BTLeafNode::initialize()
{
  int eKey = -1;
  RecordId eRid = {-1, -1};
  char buf[LEAF_ENTRY_SIZE];  

  // Set all entries to empty
  for (int i = 0; i < LEAF_MAX_ENTRIES; i++)
  {
     convertToChar(eKey, eRid, buf);
	
     for (int j = 0; j < LEAF_ENTRY_SIZE; j++)
     {
	buffer[i*LEAF_ENTRY_SIZE+j] = buf[j];	
     }
  }

  // Set the next node pointer to empty (i.e. null pointer)
  this->setNextNodePtr(-1);
}


/********************************************************************
 *   BTNonLeafNode methods
 *******************************************************************/



/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{ 
  RC rc = pf.read(pid, buffer);
  return rc;
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{
  RC rc = pf.write(pid, buffer);
  return rc;
}


/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{ 
  for (int i = 0; i < NL_MAX_KEYS; i++)
  {
     int key;
     int index = i*(sizeof(int) + sizeof(PageId)) + NL_FIRST_KEY;
     convertToInt(buffer, index, key);
     if (key == -1)
     {
       return i;
     }
  }

  return NL_MAX_KEYS;
}


/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{ 
  int numKeys = this->getKeyCount();
  if (numKeys >= NL_MAX_KEYS)
  {
     return RC_NODE_FULL;
  }

  int index = numKeys*(sizeof(int) + sizeof(PageId)) + NL_FIRST_KEY;
  for (int i = 0; i < numKeys; i++)
  {
     int oKey;
     convertToInt(buffer, i*(sizeof(int) + sizeof(PageId)) + NL_FIRST_KEY, oKey);
    
     if (key < oKey)
     {
        index = i*(sizeof(int) + sizeof(PageId)) + NL_FIRST_KEY;
        break;
     }
  }

  for (int i = numKeys*(sizeof(int) + sizeof(PageId)) + NL_FIRST_KEY - 1; i >= index; i--)
  {
     buffer[i] = buffer[i-4];
  }

  convertToChar(key, buffer, index);
  convertToChar(pid, buffer, index+sizeof(int));

  return 0;
}

/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{
  if (this->getKeyCount() < NL_MAX_KEYS)
  {
    return RC_INVALID_FILE_FORMAT;
  }

  // The first key in the new node
  int mid = (int) (NL_MAX_KEYS / 2) + 1;

  // Set the middle node that will be pushed up
  int mk;
  convertToInt(buffer, (mid-1)*(sizeof(int)+sizeof(PageId))+sizeof(PageId), mk);
  midKey = mk;
 
  convertToChar(-1, buffer, (mid-1)*(sizeof(int)+sizeof(PageId))+sizeof(PageId));

  // Initialize the sibling
  int spk;
  PageId lp, rp;

  convertToInt(buffer, mid*(sizeof(int)+sizeof(PageId)), lp);
  convertToInt(buffer, mid*(sizeof(int)+sizeof(PageId))+sizeof(PageId), spk);
  convertToInt(buffer, (mid+1)*(sizeof(int)+sizeof(PageId)), rp);

  sibling.initializeRoot(lp, spk, rp);

  convertToChar(-1, buffer, mid*(sizeof(int)+sizeof(PageId)));
  convertToChar(-1, buffer, mid*(sizeof(int)+sizeof(PageId))+sizeof(PageId));
  convertToChar(-1, buffer, (mid+1)*(sizeof(int)+sizeof(PageId)));

  // Copy over the remaining key/pid pairs
  for (int i = mid+1; i < NL_MAX_KEYS; i++)
  {
    int k;
    PageId p;
    convertToInt(buffer, i*(sizeof(int)+sizeof(PageId))+sizeof(PageId), k);
    convertToInt(buffer, (i+1)*(sizeof(int)+sizeof(PageId)), p);
    sibling.insert(k, p);
 
    convertToChar(-1, buffer, i*(sizeof(int)+sizeof(PageId))+sizeof(PageId));
    convertToChar(-1, buffer, (i+1)*(sizeof(int)+sizeof(PageId)));
  }

  return 0; 
}

/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{ 
  int numKeys = this->getKeyCount();

  for (int i = 0; i < numKeys; i++)
  {
    int key;
    convertToInt(buffer, i*(sizeof(int) + sizeof(PageId))+NL_FIRST_KEY, key);

    if (searchKey < key)
    {
       int tPid;
       convertToInt(buffer, i*(sizeof(int) + sizeof(PageId)), tPid);
       pid = tPid;
       return 0;
    }
  }
  
  int tPid;
  convertToInt(buffer, numKeys*(sizeof(int)+sizeof(PageId)), tPid);
  pid = tPid;

  return 0; 
}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId lpid, int key, PageId rpid)
{
   for (int i = 0; i < PageFile::PAGE_SIZE / sizeof(int); i++)
   {
      convertToChar(-1, buffer, i*sizeof(int));
   }

   convertToChar(lpid, buffer, 0);
   convertToChar(key, buffer, sizeof(PageId));
   convertToChar(rpid, buffer, sizeof(PageId) + sizeof(int));

   return 0;
}

void BTNonLeafNode::convertToChar(int conv, char* buffer, int index)
{
  int* bint = (int*) buffer;
  *(bint+index/sizeof(int)) = conv;
  buffer = (char*) bint;
}

void BTNonLeafNode::convertToInt(char* buffer, int index, int& result)
{
  int* bint = (int*) buffer;
  result = *(bint+index/sizeof(int));
}
