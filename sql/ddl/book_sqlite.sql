--
-- Creating a sample table.
--



--
-- Table Book
--
DROP TABLE IF EXISTS Book;
CREATE TABLE Book (
    "id" INTEGER PRIMARY KEY NOT NULL,
    "bookTitle" TEXT NOT NULL,
    "bookAuthor" TEXT NOT NULL,
    "imageLink" TEXT NOT NULL
);
