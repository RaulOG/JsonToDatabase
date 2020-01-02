# JsonToDatabase

JsonToDatabase is an application which reads json files and stores their contents into database.

## The exercise

### Primary goal

- Data within json must be stored ([Data notes](#data-notes))
- Process can be restored where it was last in case it is interrupted ([Restoring the process](#restoring-the-process))

### Secondary goals
- Design application for growth: a customer who will demand new features ([Growth considerations](#growth-considerations))
- Solid, not exaggerated database model
- Only process records with age between 18 and 65 (or unknown)

### Bonus goals
- Consider a file x500 as large as the one provided, for a total of 5 million records ([Scaling the process](#scaling-the-process))
- The process is easy to deploy for a different data format such as XML or CSV ([Supporting a new file extension](#supporting-a-new-file-extension))
- Only process records whose credit card number contains 3 consecutive same digits

### Additional
- Process does not store duplicated entries ([Skipping duplicates](#skipping-duplicates))

## The solution

### Restoring the process
The CustomerImportService detects when a process on a file has already started by finding customers in the database that match the given file name.

If so happens, the process will be restored by instructing the JsonReader to read from the last stored index.

### Growth considerations

Given that we do not know much information about the nature of the project or the customer's needs, the growth considerations applied are mostly based on intuition.

- The data importing tool might want to be used from a web interface. The system is ready for such use since we delegate the responsibility to run the import to the CustomerImportService and not the CustomerImportCommand.
- Employing our ReaderFactory and the ReaderInterface allows us to easily create new readers for new file extensions.
- A battery of well written acceptance tests have been created to guarantee that any product increments will not break already existing functionality, easing the capacity to evolve the product.

### Scaling the process

Should the files to be read become large enough, the process would take large amounts of time.

A possible solution to scaling the process is to let the CustomerImportService delegate the responsibility to evaluate and store each entry individually using jobs and queue workers. For example, every batch of 100 read entries, the CustomerImportService could create a job. In such case, more work would be required to adjust the ability of the CustomerImportProcess to restore the process when it is interrupted.

### Supporting a new file extension

To support a new file extension, you must take two steps:
- Create a new reader class which implements the ReaderInterface.
- Instruct the ReaderFactory to retrieve your new reader when the given file extension is detected.

A JsonAdapter has been created for the JsonReader composer dependency in order to have our ReaderInterface implemented. Implementing a new reader might involve creating a new adapter.

You may have a look at the already created CsvAdapter and XMLAdapter classes, which only require that you implement their ReaderInterface methods to work.

### Skipping duplicates

A hash column has been created into customers table and made unique. The hash is created using SHA512 and based on all customer information concatenated.

Whenever a duplicated customer is processed, there will be a CustomerDuplicatedException and that entry will be skipped.

There is a very small chance of hash collision between two different entries. That chance has been considered negligible.

## Data notes

The json file is expected to contain the following customer information:

- name (E.g.: Prof. Simeon Green)
- address (E.g..: 328 Bergstrom Heights Suite 709 49592 Lake Allenville)
- checked (E.g.: False)
- description (E.g.: Beatae adipisci quae dolores possimus similique impedit laudantium)
- interest (E.g.: unleash back-end content)
- date_of_birth (E.g.: 1989-03-21T01:11:13+00:00, 15\/09\/1978, 1966-07-15 00:00:00)
- email (E.g.: dimitri81@watsica.net)
- account (E.g.: 7160713229)
- credit_card
    - type (E.g.: Visa, Discover Card)
    - number (E.g.: 4929658516333333)
    - name (E.g.: Sarah Purdy Sr.)
    - expirationDate (E.g.: 01\/19)
