CREATE TABLE "tableselect" (
	"xid"	INTEGER NOT NULL,
	"name"	TEXT,
	"database"	TEXT,
	"schema"	TEXT,
	"active"	INTEGER DEFAULT 1,
    "createdon" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("xid")
)