#include <stdio.h>
#include "BTreeNode.cc"
#include "PageFile.cc"
#include "RecordFile.cc"

int main(int argc, const char* argv[])
{
	BTLeafNode n;
	n.initialize();
	printf("Num: %i, Next: %i\n", n.getKeyCount(), n.getNextNodePtr());

	RecordId rid = {0, 0};
	
	int ok;
	RecordId orr;

	for (int i = 0; i < BTLeafNode::LEAF_MAX_ENTRIES; i++)
	{
		n.insert(i,rid);
	}
	
	printf("Num: %i\n", n.getKeyCount());

	RC res = n.insert(86, rid);

	printf("Num: %i, Res: %i\n", n.getKeyCount(), res);

	BTLeafNode s;
	s.initialize();
	int sk;

	n.insertAndSplit(10, rid, s, sk);

	printf("sk: %i\n", sk);
	printf("N num: %i, S num: %i\n", n.getKeyCount(), s.getKeyCount());
}

