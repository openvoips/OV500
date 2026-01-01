/*
   Copyright (c) 2015, 2018, Oracle and/or its affiliates. All rights reserved.

























   The lines above are intentionally left blank
*/

#include <iostream>


using ::std::cout;
using ::std::endl;

int main()
try {
  cout << "Done!" << endl;
}
catch (const char *ex)
{
  cout <<"EXCEPTION: " <<ex <<endl;
}
catch (...)
{
  cout <<"GENERAL EXCEPTION " <<endl;
}
