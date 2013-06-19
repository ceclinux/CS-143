#include "PageFile.cc"
#include "CBTreeNode.cc"
#include <cstdio>
#include <sstream>

int main()
{
  printf("Compiled\n");
  
  BTNonLeafNode n;
  n.initializeRoot(1, 1, 2);
  
  printf("Initialized\n");
  
  PageId rp;
  RC rc = n.locateChildPtr(0, rp);

  printf("Locate less than, rc = %i, pid = %i (1)\n", rc, rp);

  rc = n.locateChildPtr(1, rp);

  printf("Locate equal, rc = %i, pid = %i (2)\n", rc, rp);
  
  rc = n.locateChildPtr(2, rp); 

  printf("Locate greater than, rc = %i, pid = %i (2)\n", rc, rp);

  rc = n.insert(4, 3);

  printf("Insert, rc = %i\n", rc);
  
  rc = n.locateChildPtr(0, rp);

  printf("Locate, rc = %i, pid = %i (1)\n", rc, rp);

  rc = n.locateChildPtr(1, rp);

  printf("Locate, rc = %i, pid = %i (2)\n", rc, rp);
  
  rc = n.locateChildPtr(2, rp); 

  printf("Locate, rc = %i, pid = %i (2)\n", rc, rp);

  rc = n.locateChildPtr(5, rp);

  printf("Locate, rc = %i, pid = %i (3)\n", rc, rp);

  for (int i = 2; i < BTNonLeafNode::NL_MAX_KEYS; i++)
  {
      rc = n.insert((i+1)*2, i+2);
      printf("(%i,%i) ", (i+1)*2, i+2);
      if (rc != 0)
      {
         printf("Failed: %i\n", i);
         break;
      }
  }

  printf("\n");

  rc = n.insert(256, 130);

  printf("Insert full, rc = %i\n", rc);

  BTNonLeafNode n2;
  n2.initializeRoot(-1, -1, -1);
  
  int mk;
  rc = n.insertAndSplit(256, 130, n2, mk);
  
  printf("Insert and split, rc = %i, mk = %i\n", rc, mk);
  
  rc = n2.locateChildPtr(mk, rp);
  printf("Locate mid key, rc = %i, pid = %i (65)\n", rc, rp);
  rc = n.locateChildPtr(mk, rp);
  printf("Locate mid key (orig), rc = %i, pid = %i (64)\n", rc, rp);
}
