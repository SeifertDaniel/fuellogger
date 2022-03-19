## importieren von Bestandsdaten

Tankerkönig stellt unter https://creativecommons.tankerkoenig.de/history Bestandsdaten zur Verfügung. Diese können importiert werden. Lege dazu eine Importtabelle an:

```
CREATE TABLE `import` (
  `datetime` datetime NOT NULL,
  `stationid` char(36) NOT NULL,
  `diesel` float(4,3) NOT NULL,
  `e5` float(4,3) NOT NULL,
  `e10` float(4,3) NOT NULL,
  `dieselchange` int(1) NOT NULL,
  `e5change` int(1) NOT NULL,
  `e10change` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `import`
  ADD KEY `stationid` (`stationid`);
```

Importiere die CSV-Daten in diese Tabelle.

Mit den folgenden Abfragen werden die Preisdaten in die Struktur dieses Pakets übertragen:

```
-- E5

INSERT INTO prices (
SELECT UUID(), st.ID, 'e5', im.e5, im.datetime, im.datetime FROM `import` im 
LEFT JOIN stations st ON im.stationid = st.TKID
WHERE e5change = 1 AND st.TKID IS NOT NULL
);

-- E10

INSERT INTO prices (
SELECT UUID(), st.ID, 'e10', im.e10, im.datetime, im.datetime FROM `import` im 
LEFT JOIN stations st ON im.stationid = st.TKID
WHERE e10change = 1 AND st.TKID IS NOT NULL
)

-- Diesel

INSERT INTO prices (
SELECT UUID(), st.ID, 'diesel', im.diesel, im.datetime, im.datetime FROM `import` im 
LEFT JOIN stations st ON im.stationid = st.TKID
WHERE dieselchange = 1 AND st.TKID IS NOT NULL
)
```

Im Anschluss kann die import Tabelle wieder gelöscht werden.
