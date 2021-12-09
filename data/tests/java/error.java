import java.io.*;

class Program {
    public static void main(String [] args) {
		try {
			// The name of the file to open.
			String fileOutput = "output.rez";
			
			// Assume default encoding.
			FileWriter fileWriter =
				new FileWriter(fileOutput);

			// Always wrap FileWriter in BufferedWriter.
			BufferedWriter bufferedWriter =
				new BufferedWriter(fileWriter);

			bufferedWriter.write(new String("123"));
			
			// Always close files.
			bufferedWriter.close();
			
        }
        catch(IOException ex) {
        }
    }
}