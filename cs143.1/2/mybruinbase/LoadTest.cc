#include <cstdio>
#include "Bruinbase.h"
#include "PageFile.h"
#include "RecordFile.h"
#include "BTreeNode.h"
#include "BTreeIndex.h"

int main()
{ 
   BTreeIndex b;
   RC rc = b.open("test.idx", 'w');

   if (rc != 0)
   {
      printf("Open failed\n");
      return 0;
   }

   b.readMetadata();

   IndexCursor ic;
   rc = b.locate(5999, ic); 

   if (rc != 0)
   {
      printf("Locate failed\n");
      return 0;
   }
 
   int key;
   RecordId rid;
   rc = b.readForward(ic, key, rid);
  
   if (rc != 0)
   {
     printf("Read failed\n");
     return 0;
   }

   printf("%i (%i %i)\n", key, rid.pid, rid.sid);
}
