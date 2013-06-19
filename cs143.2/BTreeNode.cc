#include "BTreeNode.h"
#include <iostream>
#define MIN(X,Y) ((X) < (Y) ? : (X) : (Y))
#define MAX(X,Y) ((X) > (Y) ? : (X) : (Y))

using namespace std;

BTLeafNode::BTLeafNode()
{
	memset(buffer, 0, PageFile::PAGE_SIZE);
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
 
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{
	//store PageFile and PageId that this node corresponds to, makes life easier
	//@done
	memset(buffer, 0, PageFile::PAGE_SIZE);
	return pf.read(pid, buffer);
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{
	//@done
	return pf.write(pid, buffer);
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{
	//@done
	int retval = 0;
	//first four bytes stores number of records in a page
	memcpy(&retval, &buffer, sizeof(int));
	
	return retval;
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{
	//@done, needs testing
	//seek to proper slot
	//insert (key, rid) pair in the buffer
	//shift buffer contents down (memcpy out, memcpy back in)
	
	int slot_size = sizeof(RecordId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	
	/*if(total_size >= PageFile::PAGE_SIZE - slot_size - sizeof(int) - sizeof(PageId)) {
		cout << "Leaf node full" << endl;
		return RC_NODE_FULL;
	}*/
	if(getKeyCount() >= 84) {
		cout << "Leaf node full" << endl;
		return RC_NODE_FULL;
	}
	
	char* iter = &(buffer[0]);
	int i = 0;
	
	/*printf("Iter: %d\n" ,iter);
	printf("Buffer: %d\n", buffer);*/
	
	/*cout << "Slot size: " << slot_size << endl;
	cout << "Total size: " << total_size << endl;
	//cout << "Looking for key: " << key << endl;
	cout << "Current num keys: " << getKeyCount() << endl;*/
	
	iter += sizeof(int);
	iter += sizeof(PageId);
	
	int currKey;
	while (iter) {
		memcpy(&currKey, iter, sizeof(int));
		//cout << "currKey is " << currKey << endl;
		if(currKey == 0) {
			break;
		}
		if (currKey < key) {
			iter += slot_size;
			i++;
		} else {
			break;
		}
	}
	
	/*cout << "Inserting key " << key << " at location " << i << endl;
	cout << "rid.pid: " << rid.pid << endl;
	cout << "rid.sid: " << rid.sid << endl;*/
	
	if (&(buffer[PageFile::PAGE_SIZE]) - iter < 20) {
		cout << "Leaf Node full 2" << endl;
		return RC_NODE_FULL;
	}
	
	char* temp = (char*)malloc(PageFile::PAGE_SIZE * sizeof(char));
	memset(temp, 0, PageFile::PAGE_SIZE);
	if(total_size > 0 && currKey != 0) {
		memcpy(temp, iter, total_size - (slot_size * i));
		memset(iter, 0, total_size - (slot_size * i));
	}

	memcpy(iter, &key, sizeof(int));
	iter += sizeof(int);
	memcpy(iter, &rid, sizeof(RecordId));

	iter += sizeof(RecordId);
	if(total_size > 0 && currKey != 0) {
		memcpy(iter, temp, total_size - (slot_size * i));
	}
	free(temp);
	
	//set the new key count
	int currKeyCount = getKeyCount();
	currKeyCount++;
	iter = &(buffer[0]);
	memcpy(iter, &currKeyCount, sizeof(int));
	
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
	//@done
	//memcpy half the current node to the sibling node
	//get the first key of the sibling node, store it in the return param
	//call insert(key, rid) to put the new {key, rid} pair in the node
	//cout << "Insert and split called" << endl;
	int slot_size = sizeof(RecordId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	int i = 0;
	int retval = 0;
	
	char* iter = &(buffer[0]);
	iter += sizeof(int);
	iter += sizeof(PageId);
	
	while(i < (getKeyCount() / 2)) {
		iter += slot_size;
		i++;
	}
	
	//increment once more to get past the last record
	//iter += slot_size;
	char* temp = (char*)malloc(1024*sizeof(char));
	char* head = temp;
	//save the last half of the data
	memset(temp, 0, 1024*sizeof(char));
	memcpy(temp, iter, total_size - (slot_size * (getKeyCount()/2)));

	
	//remove old data from current node
	memset(iter, 0, total_size - (slot_size * (getKeyCount()/2)));
	int newKey = getKeyCount() / 2;
	memcpy(buffer, &newKey, sizeof(int));
	
	//extract ALL the data from the original node
	//insert them into the new node using the insert function
	while(temp) {
		int k;
		RecordId r;
		memcpy(&k, temp, sizeof(int));
		if(k == 0) {
			break;
		}
		temp += sizeof(int);
		memcpy(&r, temp, sizeof(RecordId));
		temp += sizeof(RecordId);
		if(r.sid == 0 && r.pid == 0 && k != 272) {
			int q = r.sid;
			cout << "rid error!" << endl;
		}
		if( sibling.insert(k, r) != 0 ) {
			free(head);
			return RC_FILE_WRITE_FAILED;
		}
	}
	
	free(head);
	
	//store the first key in siblingKey
	RecordId r;
	if( sibling.readEntry(0, siblingKey, r) != 0) {
		return RC_FILE_READ_FAILED;
	}
	
	//insert the new key that we're trying to insert
	if(key < siblingKey) {
		if( insert(key, rid) != 0 ) {
			return RC_FILE_WRITE_FAILED;
		}
	} else {
		if( sibling.insert(key, rid) != 0 ) {
			return RC_FILE_WRITE_FAILED;
		}
	}
	
	if( sibling.readEntry(0, siblingKey, r) != 0) {
		return RC_FILE_READ_FAILED;
	}
	
	return retval; 
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
	//@done
	//search through the buffer until we find a key <= searchKey
	//return entry number (slot number) in eid
	
	int slot_size = sizeof(RecordId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	char* iter = &(buffer[0]);
	int curr_key;
	int i = 0;
	
	iter += sizeof(int);
	
	while(iter) {
		memcpy(&curr_key, iter, sizeof(int));
		if(curr_key >= searchKey || curr_key == 0) {
			eid = i;
			break;
		}
		i++;
		iter += slot_size;
	}
	
	return 0; 
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
	//@done
	//seek to eid in the buffer
	//extract parameters and put in return values
	int slot_size = sizeof(RecordId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	char* iter = &(buffer[0]);
	int i = 0;
	
	iter += sizeof(int);
	iter += sizeof(PageId);
	while(iter && i < eid) {
		iter += slot_size;
		i++;
	}
	memcpy(&key, iter, sizeof(int));
	iter += sizeof(int);
	memcpy(&rid, iter, sizeof(RecordId));
	
	return 0; 
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{ 
	//@done
	//get the next pid from the PageFile, will be null if no next node
	PageId pid;
	
	char* iter = &(buffer[0]);
	iter += sizeof(int);
	
	memcpy(&pid, iter, sizeof(PageId));
	
	return pid; 
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{ 
	//@done
	//on split, call this function to set pointer to the next node at the end of the buffer
	char* iter = &(buffer[0]);
	iter += sizeof(int);
	
	memcpy(iter, &pid, sizeof(PageId));
	
	return 0; 
}

BTNonLeafNode::BTNonLeafNode() {
	memset(buffer, 0, PageFile::PAGE_SIZE);
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{
	//store PageFile and PageId that this node corresponds to, makes life easier
	//@done
	return pf.read(pid, buffer); 
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{
	//@done
	return pf.write(pid, buffer);
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{
	//@done
	int retval = 0;
	//first four bytes stores number of records in a page
	memcpy(&retval, &buffer, sizeof(int));
	
	return retval;
}


/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{ 
	//@done
	//seek to proper slot
	//insert (key, pid) pair in the buffer
	//shift buffer contents down (memcpy out, memcpy back in)
	
	int slot_size = sizeof(PageId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	char* iter = &(buffer[0]);
	int i = 0;
	
	if(total_size >= PageFile::PAGE_SIZE - slot_size) {
		cout << "Non-leaf node full" << endl;
		return RC_NODE_FULL;
	}
	
	//skip number of keys, skip first pid
	iter += sizeof(int);
	iter += sizeof(PageId);
	
	int currKey;
	while (iter) {
		memcpy(&currKey, iter, sizeof(int));
		if(currKey == 0) {
			break;
		}
		if (currKey < key) {
			iter += slot_size;
			i++;
		} else {
			break;
		}
	}
	
	if(currKey == key) {
		//update
		cout << "updating key" << endl;
		iter += sizeof(int);
		memcpy(iter, &pid, sizeof(PageId));
		return 0;
	}
	
	if (iter == &(buffer[PageFile::PAGE_SIZE]))
		return RC_FILE_WRITE_FAILED;
	
	char* temp = (char*) malloc(PageFile::PAGE_SIZE * sizeof(char));
	memset(temp, 0, PageFile::PAGE_SIZE);
	
	
	if(total_size > 0 && currKey != 0) {
		memcpy(temp, iter, total_size - (slot_size * i));
		memset(iter, 0, total_size - (slot_size * i));// - sizeof(int) - sizeof(PageId));
	}
	memcpy(iter, &key, sizeof(int));
	iter += sizeof(int);
	memcpy(iter, &pid, sizeof(PageId));
	iter += sizeof(PageId);
	if(total_size > 0 && currKey != 0) {
		memcpy(iter, temp, total_size - (slot_size * i));
	}
	free(temp);
	
	//set the new key count
	int currKeyCount = getKeyCount();
	currKeyCount++;
	iter = &(buffer[0]);
	memcpy(iter, &currKeyCount, sizeof(int));
	
	return 0;
}

RC BTNonLeafNode::readEntry(int eid, int& key) {
	int slot_size = sizeof(PageId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	int i = 0;
	
	char* iter = &(buffer[0]);
	iter += sizeof(int);
	
	while(iter) {
		iter += sizeof(PageId);
		if(i == eid) {
			memcpy(&key, iter, sizeof(int));
			break;
		}
		iter += slot_size;
	}
	
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
	//@done
	//memcpy half the current node to the sibling node
	//get the first key of the sibling node, store it in the return param
	//call insert(key, rid) to put the new {key, rid} pair in the node
	int slot_size = sizeof(PageId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	int i = 0;
	
	char* iter = &(buffer[0]);
	iter += sizeof(int);
	
	while(i < (getKeyCount() / 2)) {
		iter += slot_size;
		i++;
	}
	
	//increment once more to get past the last record
	//iter += slot_size;
	char* temp = (char*) malloc(PageFile::PAGE_SIZE * sizeof(char));
	char* head = temp;
	//save the last half of the data
	memset(temp, 0, 1024*sizeof(char));
	memcpy(temp, iter, total_size - (slot_size * (getKeyCount() / 2)));// - sizeof(int));
	
	//remove old data from current node
	memset(iter, 0, total_size - (slot_size * (getKeyCount() / 2)));// - sizeof(int));
	int newKey = getKeyCount() / 2;
	memcpy(buffer, &newKey, sizeof(int));
	
	//extract ALL the data from the original node
	//insert them into the new node using the insert function
	while(temp) {
		int k;
		PageId p;
		memcpy(&k, temp, sizeof(int));
		if(k == 0) {
			break;
		}
		temp += sizeof(int);
		memcpy(&p, temp, sizeof(PageId));
		temp += sizeof(PageId);
		if( sibling.insert(k, p) != 0) {
			free(head);
			return RC_FILE_WRITE_FAILED;
		}
	}
	
	free(head);
	
	if( sibling.readEntry(0, midKey) != 0 ) {
		return RC_FILE_READ_FAILED;
	}
	
	//insert the new key that we're trying to insert
	if(key < midKey) {
		if( insert(key, pid) != 0 ){
			return RC_FILE_WRITE_FAILED;
		}
	} else {
		if( sibling.insert(key, pid) != 0 ) {
			return RC_FILE_WRITE_FAILED;
		}
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
	//@done
	//seek to search key
	//memcpy out the pid

	int slot_size = sizeof(PageId) + sizeof(int);
	int total_size = getKeyCount() * slot_size;
	char* iter = &(buffer[0]);
	int curr_key;
	
	iter += sizeof(int);
	iter += sizeof(int);
	
	while(iter) {
		memcpy(&curr_key, iter, sizeof(int));
		if(curr_key != 0 && curr_key < searchKey) {
			iter += sizeof(int);
			//memcpy(&pid, iter, sizeof(PageId));
			//break;
		} else {
			iter -= sizeof(PageId);
			memcpy(&pid, iter, sizeof(PageId));
			break;
		}
		iter += slot_size;
	}
 
	return 0;	
}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{
	//@done
	//Get iterator
	//place data in buffer
	char* iter = &(buffer[0]);
	int k = 1;
	iter += sizeof(int);
	
	memcpy(iter, &pid1, sizeof(PageId));
	iter += sizeof(PageId);
	memcpy(iter, &key, sizeof(int));
	iter += sizeof(int);
	memcpy(iter, &pid2, sizeof(PageId));
	
	memcpy(buffer, &k, sizeof(int));
	
	return 0; 
}
