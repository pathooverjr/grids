CREATE TABLE "controlselect" (
	"xid"	INTEGER NOT NULL,
	"displayorder"	INTEGER,
	"type"	TEXT,
	"context"	TEXT DEFAULT 'grid',
	"active"	INTEGER DEFAULT 1,
    "createdon" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY("xid")
)