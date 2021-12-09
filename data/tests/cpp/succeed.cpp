#include <iostream>
#include <fstream>
using namespace std;

int main()
{
	ifstream in("INPUT.DAT");
	ofstream out("OUTPUT.REZ");

	string line;
	while (getline(in, line))
	{
		out << line << endl;
	}

	return 0;
}