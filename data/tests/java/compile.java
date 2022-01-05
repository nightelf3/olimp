import java.io.*;

class Program {
    public static void main(String [] args) {
		try {
			// The name of the file to open.
			String fileName = "input.dat";
			String fileOutput = "output.rez";

			// Use this for reading the data.
			byte[] buffer = new byte[1000];

			FileInputStream inputStream = 
				new FileInputStream(fileName);
				
			// Assume default encoding.
			FileWriter fileWriter =
				new FileWriter(fileOutput);

			// Always wrap FileWriter in BufferedWriter.
			BufferedWriter bufferedWriter =
				new BufferedWriter(fileWriter);

			while(int inputStream.read(buffer) != -1) {
				bufferedWriter.write(new String(buffer));
				bufferedWriter.newLine();
			}   

			// Always close files.
			inputStream.close();
			
			// Always close files.
			bufferedWriter.close();
			
        }
        catch(FileNotFoundException ex) {
        }
        catch(IOException ex) {
        }
    }
}