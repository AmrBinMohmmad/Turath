    DECLARE @json NVARCHAR(MAX);
    SELECT @json = BulkColumn
    FROM OPENROWSET(BULK 'C:\Users\BROO6\Downloads\DataAnnotationProject-main\DataAnnotationProject-mainfile\Qs.json', SINGLE_CLOB) AS Qs;
    SELECT *
    FROM OPENJSON ($.data)
    WITH (
        Term VARCHAR(MAX) '$.Term',
        Meaning_of_term VARCHAR(MAX) '$.Meaning_of_term',
        Dialect_type VARCHAR(20) '$.["Dialect type"]',
        Location_Recognition_question VARCHAR(MAX) '$.Location_Recognition_question',
        Cultural_Interpretation_question VARCHAR(MAX) '$.Cultural_Interpretation_question',
        Contextual_Usage_question VARCHAR(MAX) '$.Contextual_Usage_question',
        Fill_in_Blank_question VARCHAR(MAX) '$.Fill_in_Blank_question',
        True_False_question VARCHAR(MAX) '$.True_False_question',
        Meaning_question VARCHAR(MAX) '$.Meaning_question'
    );
    INSERT INTO Qs (Term, Meaning_of_term, Dialect_type, Location_Recognition_question, Cultural_Interpretation_question, Contextual_Usage_question, Fill_in_Blank_question, True_False_question, Meaning_question)
    SELECT *
    FROM OPENJSON(@json)
    WITH (
        Term VARCHAR(MAX) ,
        Meaning_of_term VARCHAR(MAX) ,
        Dialect_type VARCHAR(20) ,
        Location_Recognition_question VARCHAR(MAX) ,
        Cultural_Interpretation_question VARCHAR(MAX) ,
        Contextual_Usage_question VARCHAR(MAX) ,
        Fill_in_Blank_question VARCHAR(MAX) ,
        True_False_question VARCHAR(MAX),
        Meaning_question VARCHAR(MAX)
    );
    SELECT Term FROM Qs 
    WHERE Term = 'آزيت';