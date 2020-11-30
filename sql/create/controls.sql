CREATE TABLE IF NOT EXISTS "controls"
(
            "xid"	INTEGER NOT NULL,
            "type"	TEXT,
            "name"	TEXT,
            "desc"	TEXT,
            "href"	TEXT,
            "plugin" TEXT,
            "category" TEXT,
            "createdon" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY ("xid")
)