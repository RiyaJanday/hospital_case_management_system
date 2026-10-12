# Traceability

| ID | Requirement (input) | Implementation (output) | Verification |
|---|---|---|---|
| REQ-001 | Admin shall be able to create a case | `MedicalCaseController@store` + `cases/create.blade.php` form | Feature test: `test_admin_can_create_a_case` |
| REQ-002 | Case Number shall be unique | Unique DB index (`cases` migration) + unique rule in `MedicalCaseRequest` | Feature test: `test_duplicate_case_number_fails_with_422` |
| REQ-003 | Only Admin shall delete cases | `MedicalCasePolicy@delete` (checked in both web and API controllers) | Feature test: `test_viewer_cannot_delete_a_case` |
