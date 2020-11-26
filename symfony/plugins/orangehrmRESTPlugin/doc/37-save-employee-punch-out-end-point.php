/**
* @api {post} /attendance/punch-out 16.Save Employee Punch Out
* @apiName EmployeePunchOut
* @apiGroup User
* @apiVersion 1.2.0
* @apiUse UserDescription
*
*
* @apiParam   {String} timezone  Time Zone ( ex: "Asia/Colombo" ).
* @apiParam   {String} [note] Punch Out Note. ( ex: "Successfully Punched Out" )
* @apiParam   {Date}  datetime Date and Time Required If Current Time Editable ( ex: "2020-12-28 18:30" )
*
*
* @apiSuccessExample Success-Response:
*     HTTP/1.1 200 OK
*
*            {
*                "success": "Successfully Punched Out",
*                "id": "1",
*                "punchInDateTime": "2020-12-28 08:30",
*                "punchInTimeZone": 5.5,
*                "punchInNote": "PUNCH IN NOTE",
*                "punchOutDateTime": "2020-12-28 18:30",
*                "punchOutTimeZone": 5.5,
*                "punchOutNote": "PUNCH OUT NOTE"
*            }
*
* @apiError InvalidParameter Found.
*
* @apiErrorExample Error-Response:
*     HTTP/1.1 202 Invalid Parameter
*     {
*       "error": ["Invalid Time Zone"]
*     }
*
*
* @apiError Invalid Action.
*
* @apiErrorExample Error-Response:
*     HTTP/1.1 202 Invalid Parameter
*     {
*       "error": ["Cannot Proceed Punch Out Employee Already Punched Out"]
*     }
*
* @apiError Invalid Action.
*
* @apiErrorExample Error-Response:
*     HTTP/1.1 202 Invalid Parameter
*     {
*       "error": ["Overlapping Records Found"]
*     }
*
* @apiError Invalid Action.
*
* @apiErrorExample Error-Response:
*     HTTP/1.1 202 Invalid Parameter
*     {
*       "error": ["Datetime Cannot Be Empty"]
*     }
*
*/

