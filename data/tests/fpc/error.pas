Var S : String;
    F : TextFile;
  	O : TextFile;

begin
  Assign (F,'input.dat');
  Assign (O,'output.rez');
  Reset (F);
  Rewrite(O);
  While not Eof(f) do
    Begin
    Readln(F,S);
	  Writeln(O, 'Test');
    end;
 Close (O);
 Close (F);
end.