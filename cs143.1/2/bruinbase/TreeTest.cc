#include "BTreeIndex.cc"
#include "BTreeNode.cc"
#include "PageFile.cc"
#include "RecordFile.cc"
#include <cstdio>

int main()
{

  printf("Starting Chris' tests....\n");

  BTreeIndex b;
  b.open("test.idx", 'w');

  // Key to insert
  int key;
  // RID to insert
  RecordId rid;
  // RC returned
  RC rc;
  // Index cursor returned
  IndexCursor ic;
  // Key to return
  int retKey;
  // RID to return
  RecordId retRid;

  for (int i = 1; i < 15000; i++)
  {
     key = i;
     rid.pid = i;
     rid.sid = i;
     rc = b.insert(key, rid);
     if (rc != 0)
     {
        fprintf(stdout, "Error inserting %i, (%i %i): %i\n", key, rid.pid, rid.sid, rc);
        return 0;
     }

     for (int j = 1; j <= i; j += 85)
     {
        rc = b.locate(j, ic);
        if (rc != 0)
        {
          fprintf(stdout, "Error locating %i after %i: %i\n", j, i, rc);
          return 0;
        }
        rc = b.readForward(ic, retKey, retRid);
        if (rc != 0)
        {
          fprintf(stdout, "Error reading %i after %i: %i\n", j, i, rc);
          return 0;
        }
        if (retKey != j || retRid.pid != j || retRid.sid != j)
        {
          fprintf(stdout, "Error: inserted %i (%i %i), located %i (%i %i)\n", key, rid.pid, rid.sid, retKey, retRid.pid, retRid.sid);
          return 0;
        }
     }
  }

/*
	// Node Test
	BTNonLeafNode n;
	n.initializeRoot(0, -1, -1);

	for (int i = 1; i < MAX_NONLEAFDATA+1; i++)
	{

	  
		RC nrc = n.insert(i, i);
		if (nrc != 0)
		{
			printf("Insert failed.\n");
			printf("RC: %i, Key: %i\n", nrc, i);
			break;
		}
	}

	BTNonLeafNode n2;
	n2.initializeRoot(-1, -1, -1);
	int mid;
	RC nrc = n.insertAndSplit(MAX_NONLEAFDATA+1, MAX_NONLEAFDATA+1, n2, mid);
	printf("RC: %i, Mid: %i\n", nrc, mid);

	PageId ret;
	nrc = n.locateChildPtr(1, ret);
	printf("RC: %i, PID: %i\n", nrc, ret);
	
	nrc = n2.locateChildPtr(mid-1, ret);
	printf("RC: %i, PID: %i\n", nrc, ret);
	
	nrc = n2.locateChildPtr(mid, ret);
	printf("RC: %i, PID: %i\n", nrc, ret);

	nrc = n2.locateChildPtr(mid+1, ret);
	printf("RC: %i, PID: %i\n", nrc, ret);
	
	// Creation
	printf("Compiled\n");
	
	BTreeIndex tree;
	tree.open("test.idx", 'w');

	printf("Opened\n");

	RecordId rid = {1, 80};

	// Insert: empty tree
	RC rc = tree.insert(1, rid);

	printf("RC: %i\n", rc);
	printf("Height 1:\n");

	// Locate: tree of height 1
	IndexCursor ic;
	rc = tree.locate(1, ic);

	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);

	// Read forward
	RecordId res;
	int key;
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i, RID: %i %i\n", rc, key, res.pid, res.sid);

	// Insert: tree of height 1
	rc = tree.insert(2, rid);

	printf("RC: %i\n", rc);

	rc = tree.locate(1, ic);
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(1), RID: %i %i\n", rc, key, res.pid, res.sid);
	rc = tree.locate(2, ic);
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(2), RID: %i %i\n", rc, key, res.pid, res.sid);

	// Insert: tree of height 2
	for (int i = 0; i < 100; i++)
	{
		rc = tree.insert(i+3, rid);
		if (rc != 0)
		{
			printf("Failed: %i\n", i);
			break;
		}
	}

	// Locate: tree of height 2
	printf("Height 2:\n");
	rc = tree.locate(100, ic);
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(100), RID: %i %i\n", rc, key, res.pid, res.sid);

	// Close and open
	tree.writeMetadata();
	tree.close();
	tree.open("test.idx", 'w');
	tree.readMetadata();

	printf("Closed and opened:\n");

	rc = tree.insert(101, rid);
	printf("RC: %i\n", rc);
	rc = tree.locate(1, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(1), RID: %i %i\n", rc, key, res.pid, res.sid);
	rc = tree.locate(101, ic);
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(101), RID: %i %i\n", rc, key, res.pid, res.sid);

	// Insert: tree of height 3
	for (int i = 102; i < 120000; i++)
	{
		// i = 5545	

		rc = tree.insert(i, rid);
		if (rc != 0)
		{
			printf("Failed: %i\n", i);
			break;
		}
	}

	printf("Height 3:\n");

	// Locate: tree of height 3
	rc = tree.locate(1, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(1), RID: %i %i\n", rc, key, res.pid, res.sid);
	rc = tree.locate(86, ic);
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);

	
	rc = tree.readForward(ic, key, res);

	printf("RC: %i, Key: %i(86), RID: %i %i\n", rc, key, res.pid, res.sid);
  
	rc = tree.locate(290, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(290), RID: %i %i\n", rc, key, res.pid, res.sid);

	rc = tree.locate(775, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(775), RID: %i %i\n", rc, key, res.pid, res.sid);
	
	rc = tree.locate(2046, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(2046), RID: %i %i\n", rc, key, res.pid, res.sid);

	rc = tree.locate(4000, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(4000), RID: %i %i\n", rc, key, res.pid, res.sid);
	rc = tree.locate(5544, ic);	
	printf("RC: %i, IC: %i %i\n", rc, ic.pid, ic.eid);
	rc = tree.readForward(ic, key, res);
	printf("RC: %i, Key: %i(5544), RID: %i %i\n", rc, key, res.pid, res.sid);
*/
	printf("End of Chris' tests.\n");

}
