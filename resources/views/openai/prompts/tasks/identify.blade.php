Extract, translate and summarise the legal obligations in the legal text provided, and rephrase them as an actionable tasks.
A task is an obligation that people and organisations must comply with, and should be actionable.
Each task must be in {{ $language }}..
Each task must be 150 characters or less.
If there are no legal obligations, only return an empty JSON formatted list like this: []

Do not include legal rights in the obligations;
Some examples of legal rights are:
'any owner may do X'
'the official is authorised to do X'.

Do not include legal exemptions/exceptions in the obligations;
some examples of legal exemptions/exceptions are:
'the owner does not need to do X',
'the operator is exempt from doing X'.

Do not include obligations on government in the obligations;
some examples of obligations on government are:
'the Ministers/Administrators/Commissioners/Officials shall/must do X'.

Provide the output of tasks as a JSON formatted list of tasks. As your response needs to be machine readable, strictly only send back correctly formatted JSON format in your response - no other text is allowed. Before sending your response, check the response is a validly formatted JSON list.

Here is the legal text, provided within three back ticks:

```
{{ $text }}
```
