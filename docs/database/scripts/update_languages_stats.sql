UPDATE `languages` SET group_1 = 0;
UPDATE `languages` l, 
    (SELECT count(*) as count, language_code
        FROM users_languages JOIN users
        ON users.id = users_languages.of_user_id 
        WHERE users.group_id = 1 AND users_languages.level = 5
        GROUP BY users_languages.language_code
    ) as ul
    SET group_1 = ul.count
    WHERE l.code = ul.language_code;

UPDATE `languages` SET group_2 = 0;
UPDATE `languages` l, 
    (SELECT count(*) as count, language_code
        FROM users_languages JOIN users
        ON users.id = users_languages.of_user_id 
        WHERE users.group_id = 2 AND users_languages.level = 5
        GROUP BY users_languages.language_code
    ) as ul
    SET group_2 = ul.count
    WHERE l.code = ul.language_code;

UPDATE `languages` SET group_3 = 0;
UPDATE `languages` l, 
    (SELECT count(*) as count, language_code
        FROM users_languages JOIN users
        ON users.id = users_languages.of_user_id 
        WHERE users.group_id = 3 AND users_languages.level = 5
        GROUP BY users_languages.language_code
    ) as ul
    SET group_3 = ul.count
    WHERE l.code = ul.language_code;

UPDATE `languages` SET group_4 = 0;
UPDATE `languages` l, 
    (SELECT count(*) as count, language_code
        FROM users_languages JOIN users
        ON users.id = users_languages.of_user_id 
        WHERE users.group_id = 4 AND users_languages.level = 5
        GROUP BY users_languages.language_code
    ) as ul
    SET group_4 = ul.count
    WHERE l.code = ul.language_code;



UPDATE `languages` SET level_5 = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level = 5
   GROUP BY users_languages.language_code
  ) as ul
SET level_5 = ul.count
WHERE l.code = ul.language_code;

UPDATE `languages` SET level_4 = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level = 4
   GROUP BY users_languages.language_code
  ) as ul
SET level_4 = ul.count
WHERE l.code = ul.language_code;

UPDATE `languages` SET level_3 = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level = 3
   GROUP BY users_languages.language_code
  ) as ul
SET level_3 = ul.count
WHERE l.code = ul.language_code;

UPDATE `languages` SET level_2 = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level = 2
   GROUP BY users_languages.language_code
  ) as ul
SET level_2 = ul.count
WHERE l.code = ul.language_code;

UPDATE `languages` SET level_1 = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level = 1
   GROUP BY users_languages.language_code
  ) as ul
SET level_1 = ul.count
WHERE l.code = ul.language_code;

UPDATE `languages` SET level_0 = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level = 0
   GROUP BY users_languages.language_code
  ) as ul
SET level_0 = ul.count
WHERE l.code = ul.language_code;

UPDATE `languages` SET level_unknown = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, language_code
   FROM users_languages JOIN users
       ON users.id = users_languages.of_user_id
   WHERE users_languages.level IS null
   GROUP BY users_languages.language_code
  ) as ul
SET level_unknown = ul.count
WHERE l.code = ul.language_code;



UPDATE `languages` SET sentences = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, lang
   FROM sentences
   GROUP BY lang
  ) as s
SET sentences = s.count
WHERE l.code = s.lang;

UPDATE `languages` SET audio = 0;
UPDATE `languages` l,
  (SELECT count(*) as count, lang
   FROM sentences
   WHERE hasaudio = 'shtooka'
   GROUP BY lang
  ) as s
SET audio = s.count
WHERE l.code = s.lang;