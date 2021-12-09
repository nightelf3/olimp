Var S : String;
    F : TextFile;
  	O : TextFile;

begin
  Assign (F,'input.dat');
  Assign (O,'output.rez');
  Reset (F);
  Rewrite(O);
  While True do
    Begin
    end;
 Close (O);
 Close (F);
end.