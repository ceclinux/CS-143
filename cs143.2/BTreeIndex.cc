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
#include <fcntl.h>
#include <sys/stat.h>
#include <iostream>

using namespace std;

Node::Node(PageId pid) {
	this->pid = pid;
}

PageId Node::getPid() {
	return this->pid;
}

/*
 * DoubleLinkedList code
 */
 
DoubleLinkedList::DoubleLinkedList() {
	head = tail = 0;
	length = 0;
}

int DoubleLinkedList::insert(PageId pid) {
	if(length == 0) {
		head = new Node(pid);
		tail = head;
		tail->next = 0;
		head->prev = 0;
		length == 1;
	} else {
		Node* temp = new Node(pid);
		tail->next = temp;
		temp->next = 0;
		temp->prev = tail;
		tail = temp;
		length++;
	}
	return 0;
}

int DoubleLinkedList::getValueAtTail(PageId& pid) {
	pid = tail->pid;
	Node* temp;
	if(tail->prev) {
		temp = tail->prev;
	} else {
		return -1;
	}
	delete tail;
	tail = temp;
	return 0;
}

int DoubleLinkedList::destructList() {
	while(head != tail) {
		Node* temp = tail->prev;
		delete tail;
		tail = temp;
	}
	delete head;
	
	return 0;
}

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
	//@done
	//initialize other data if necessary
    rootPid = -1;
	treeHeight = 0;
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
	//@done
	this->mode = mode;
	if( pf.open(indexname, mode) != 0) {
		return RC_FILE_OPEN_FAILED;
	}
	
	//cout << "Opened the page file" << endl;
	//get the private data
	
	if(mode == 'w') {
		return 0;
	}
	
	char buffer[PageFile::PAGE_SIZE];
	if( pf.read(0, buffer) != 0) {
		return RC_FILE_READ_FAILED;
	}
	
	//cout << "Read from the pagefile" << endl;
	
	char* iter = &(buffer[0]);
	memcpy(&rootPid, iter, sizeof(PageId));
	iter += sizeof(PageId);
	memcpy(&treeHeight, iter, sizeof(int));
	
	/*cout << "Root Pid is: " << rootPid << endl;
	cout << "Tree height is: " << treeHeight << endl;*/
	
	return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
	//@done, for the most part.
	//write data in memory out to the index file, if file opened for write
	char buffer[PageFile::PAGE_SIZE];
	char* iter = &(buffer[0]);
	if( mode == 'w' ) {
		memset(iter, 0, PageFile::PAGE_SIZE);
		memcpy(iter, &rootPid, sizeof(PageId));
		iter += sizeof(PageId);
		memcpy(iter, &treeHeight, sizeof(PageId));
		if( pf.write(0, buffer) != 0) {
			cout << "Could not write root information" << endl;
			return RC_FILE_WRITE_FAILED;
		}
	}
	//write private data to page 0 in the file
	
    return pf.close();
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{	
	//first, we find the location to insert to and get it in the IndexCursor
	IndexCursor cursor;
	
	//variables for loop
	PageId p;
	PageId oldPid;
	BTNonLeafNode sib;
	int midKey;
	BTNonLeafNode node;
	BTLeafNode leaf;
	
	if( locate(key, cursor) != 0) {
		//cout << "Could not locate value " << key << endl;
	}
	
	//now, we need to get the node and insert the new data
	if(treeHeight == 1) {
		leaf.read(cursor.pid, pf);
		if( leaf.insert(key, rid) != 0) {
			goto split;
		}
		leaf.write(cursor.pid, pf);
		return 0;
	}

	if( leaf.read(cursor.pid, pf) != 0) {
		//return RC_FILE_READ_FAILED;
		/*char buffer[PageFile::PAGE_SIZE];
		char* iter = &(buffer[0]);
		memcpy(iter, &key, sizeof(int));
		iter += sizeof(int);
		memcpy(iter, &rid, sizeof(RecordId));
		leaf.write(cursor.pid, pf);*/
	}
	
	/*cout << "Inserting key: " << key << endl;
	cout << "RecordId info: " << endl;
	cout << "\trid.pid " << rid.pid << endl;
	cout << "\trid.sid " << rid.sid << endl;*/
	
	if( leaf.insert(key, rid) != 0) {
		//cout << "Could not insert." << endl;
		//couldn't insert to the node, we need to split.
	split:
		BTLeafNode sibling;
		int siblingKey;
		leaf.insertAndSplit(key, rid, sibling, siblingKey);
		
		//sets the new pointer to the next node
		PageId nextNodePtr = leaf.getNextNodePtr();
		sibling.setNextNodePtr(nextNodePtr);
		leaf.setNextNodePtr(pf.endPid());
		
		if( leaf.write(cursor.pid, pf) != 0) {
			return RC_FILE_WRITE_FAILED;
		}
		
		if( sibling.write(pf.endPid(), pf) != 0) {
			return RC_FILE_WRITE_FAILED;
		}
		//now we need to pop back up to the parent and insert into the parent node
		//we can do this by iterating through a doubly linked list of all the PageIds that we've visited in locate
		//locate will maintain this list, so we get the tail and go back to the head
		//we get the non-leaf node specified by that PageId, we try to insert the key to it
		//if not, we split the node and we go back to the next parent, until we go to the root
		//if we need to create a new root, we call initializeRoot with the key and the two pointers
		//these two pointers will go to the new node that we just split from root and the current root
		//after we do a search, we have to destruct the entire linked list
		//@done
		//@todo - need to handle case where we pop back up to the root node and need a new root
		if(treeHeight == 1) {
			BTNonLeafNode root;
			root.initializeRoot(cursor.pid, siblingKey, pf.endPid()-1);
			if(root.write(pf.endPid(), pf) != 0) {
				return RC_FILE_WRITE_FAILED;
			}
			rootPid = pf.endPid() - 1;
			treeHeight++;
			return 0;
		}
		
		int i = 0;
		do {
			oldPid = p;
			list.getValueAtTail(p);
			
			if( node.read(p, pf) != 0) {
				return RC_FILE_READ_FAILED;
			}
			
			if(i == 0) {
				//@todo this function call is the problem - which PageId do we insert for the key? condition is wrong. fix it.
				/*cout << "Sibling key: " << siblingKey << endl;
				cout << "pf.endPid(): " << pf.endPid() << endl;
				cout << "cursor.pid: " << cursor.pid << endl;*/
				if(node.insert(siblingKey, pf.endPid()-1) == 0) {
					//inserted the key to the parent successfully, exit loop
					if( node.write(p, pf) != 0) {
						return RC_FILE_WRITE_FAILED;
					}
					break;
				} else {
					node.insertAndSplit(siblingKey, cursor.pid, sib, midKey);
					sib.write(pf.endPid(), pf);
					if( node.write(p, pf) != 0) {
						return RC_FILE_WRITE_FAILED;
					}
				}
			} else {
				//not first iteration, we want to make sure we're properly storing PageIds when we pop through the list
				if(node.insert(midKey, oldPid) == 0) {
					//inserted the key to the parent successfully, exit loop
					if( node.write(p, pf) != 0) {
						return RC_FILE_WRITE_FAILED;
					}
					break;
				} else {
					node.insertAndSplit(midKey, oldPid, sib, midKey);
					sib.write(pf.endPid(), pf);
					
					//handle root case
					if(p == rootPid) {
						BTNonLeafNode root;
						root.initializeRoot(p, midKey, pf.endPid()-1);
						if(root.write(pf.endPid(), pf) != 0) {
							return RC_FILE_WRITE_FAILED;
						}
						rootPid = pf.endPid() - 1;
						treeHeight++;
					}
					if( node.write(p, pf) != 0) {
						return RC_FILE_WRITE_FAILED;
					}
					break;
				}
			}
			i++;
		} while(p != rootPid);
	}
	//write out the node for good
	//cout << "Writing leaf node to pid " << (int)cursor.pid << endl;
	if( leaf.write(cursor.pid, pf) != 0) {
		//cout << "Failed leaf node write" << endl;
		return RC_FILE_WRITE_FAILED;
	}
	//destruct the linked list
	list.destructList();
	//we're done now
	
    return 0;
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
	//@done
	//start from root, go through nodes in memory
	//call locate on leaf nodes
	int retval = 0;
	//cout << "Trying to locate key " << searchKey << endl;
	
	if(treeHeight == 0) {
		cursor.pid = 1;
		cursor.eid = 0;
		treeHeight++;
		rootPid = 1;
	} else if(treeHeight == 1) {
		//tree is of size 1, just a node at the root.
		BTLeafNode root;
		int eid;
		/*cout << "Node at root only" << endl;
		cout << "Reading from rootPid = " << rootPid << endl;*/
		if( root.read(rootPid, pf) != 0) {
			return RC_FILE_READ_FAILED;
		}
		
		if( root.locate(searchKey, eid) != 0) {
			return RC_NO_SUCH_RECORD;
		}
		cursor.pid = rootPid;
		cursor.eid = eid;
	} else {
		//need to loop through nodes to get data.
		BTNonLeafNode node;
		if( node.read(rootPid, pf) != 0) {
			return RC_FILE_READ_FAILED;
		}
		
		int level = 1;
		while(level < treeHeight) {
			//we insert the nextPid to our linked list to maintain it.
			if(level == 1) {
				list.insert(rootPid);
			} else {
				list.insert(nextPid);
			}
			
			if( node.locateChildPtr(searchKey, nextPid) != 0) {
				return RC_NO_SUCH_RECORD;
			}
			
			
			if( node.read(nextPid, pf) != 0) {
				return RC_FILE_READ_FAILED;
			}
			level++;
		}
		
		//pop leaf node off list
		//PageId p;
		//list.getValueAtTail(p);
		
		cursor.pid = nextPid;
		cursor.eid = 0;
	}
	
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
	//@done
	//get specified value.
	
	BTLeafNode node;
	if(node.read(cursor.pid, pf) != 0) {
		return RC_FILE_READ_FAILED;
	}
	
	if(node.readEntry(cursor.eid, key, rid) != 0) {
		return RC_INVALID_RID;
	}
	
	if (cursor.eid < node.getKeyCount() - 1) {
		cursor.eid++;
	} else {
		cursor.eid = 0;
		int next;
		next = node.getNextNodePtr();
		if(next == 0) {
			return RC_END_OF_TREE;
		}
		cursor.pid = next;
	}	
	
    return 0;
}
