import java.io.*;

class Program {
    public static void main(String [] args) {
		int i = 0;
		int k = 100 / i;
		try {
			// The name of the file to open.
			String fileOutput = "output.rez";
			
			// Assume default encoding.
			FileWriter fileWriter =
				new FileWriter(fileOutput);

			// Always wrap FileWriter in BufferedWriter.
			BufferedWriter bufferedWriter =
				new BufferedWriter(fileWriter);

			bufferedWriter.write(Integer.toString(k));
			
			// Always close files.
			bufferedWriter.close();
			
        }
        catch(IOException ex) {
        }
    }
}