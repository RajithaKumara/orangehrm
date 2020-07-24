$(document).ready(function() {

    $('#btnSave').click(function() {
         $('#frmOpenIdProvider').submit();
        $('#oauth_client_update').val( '');
    });


    $('#oauth_client_update').hide();

    $("#resultTable tr").click(function(){
        $(this).addClass('selected').siblings().removeClass('selected');
        setUpdateValues($(this));

    });


    $('#btnCancel').click(function() {
        $('#openid').hide();
        $('.top').show();
        $('#btnDelete').show();
        $('.checkbox-col').show();
        $('#resultTable td:nth-child(1)').show();
        validator.resetForm();
    });

   $('#btnDelete').attr('disabled', 'disabled');


    $("#ohrmList_chkSelectAll").click(function() {
        if($(":checkbox").length == 1) {
            $('#btnDelete').attr('disabled','disabled');
        }
        else {
            if($("#ohrmList_chkSelectAll").is(':checked')) {
                $('#btnDelete').removeAttr('disabled');
            } else {
                $('#btnDelete').attr('disabled','disabled');
            }
        }
    });

    $(':checkbox[name*="chkSelectRow[]"]').click(function() {
        if($(':checkbox[name*="chkSelectRow[]"]').is(':checked')) {
            $('#btnDelete').removeAttr('disabled');
        } else {
            $('#btnDelete').attr('disabled','disabled');
        }
    });

    $('#btnDelete').click(function(){
        $('#frmList_ohrmListComponent').submit(function(){
            $('#deleteConfirmation').dialog('open');
            return false;
        });
    });

    $('#frmList_ohrmListComponent').attr('name','frmList_ohrmListComponent');
    $('#dialogDeleteBtn').click(function() {
        document.frmList_ohrmListComponent.submit();
    });
    $('#dialogCancelBtn').click(function() {
        $("#deleteConfirmation").dialog("close");
    });

    $('#entitlements_employee_empName').result(function(event, item) {
        $('#entitlements_employee_empId').val(item.id)
            .data('item.name', item.name)
            .valid();
    });

    $('#oAuthClientRegistrationForm').validate({
        ignore: [],
        rules: {
            'oauth[client_id]' : {
                required:true,
                maxlength: 80
            },
            'oauth[client_secret]' : {
                maxlength: 80
            },
            'oauth[redirect_uri]' : {
                maxlength: 2000
            },
            'oauth[client_grant_types]' : {
                required:true,
            },
            'oauth[client_scopes]' : {
                required:true,
            }

        },
        messages: {
            'oauth[client_id]': {
                required: "Required",
                maxlength: "Max Length 80"

            },
            'oauth[client_secret]': {
                maxlength: "Max Length 80"
            },
            'oauth[redirect_uri]': {
                maxlength: "Max Length 2000"
            },
            'oauth[client_grant_types]': {
                required: "Required",
            },
            'oauth[client_scopes]': {
                required: "Required",
            }

        }

    });



});

function setUpdateValues(selectedTableRow){

    $(selectedTableRow).find('td').each (function( column, td) {

        if(column == 0) {
            var clientId =  $(td).html();
            $('#oauth_client_id').val($(clientId).val());
        }
        if(column == 2) {
            $('#oauth_client_secret').val( $(td).html());
        }
        if(column == 3) {
            $('#oauth_redirect_uri').val( $(td).html());
        }
        if (column == 4) {
            $("input[name='oauth[client_grant_types][]']").prop("checked",false);
            const grantTypeString = $(td).html();
            if (grantTypeString !== '') {
                const grantTypes = getArrayFromCommaSeparatedString(grantTypeString);
                console.log(grantTypes);
                grantTypes.forEach(function (grantType) {
                    const id = '#oauth_client_grant_types_' + grantType;
                    $(id).val(grantTypeString).prop("checked", true);
                });
            }
        }
        if (column == 5) {
            $("input[name='oauth[client_scopes][]']").prop("checked",false);
            const scopesString = $(td).html();
            if (scopesString !== '') {
                const scopes = getArrayFromCommaSeparatedString(scopesString);
                console.log(scopes);
                scopes.forEach(function (scope) {
                    const id = '#oauth_client_scopes_' + scope;
                    $(id).val(scopesString).prop("checked", true);
                });
            }
        }

    });

    $('#oauth_client_update').val( 'update');


}

function getArrayFromCommaSeparatedString(commaSeparatedString) {
    const array = commaSeparatedString.split(',');
    return array.map(function (value) {
        return value.trim();
    });
}

function resetFields(){
    $('#oauth_client_id').val('');
    $('#oauth_client_secret').val('');
    $('#oauth_redirect_uri').val('');
    $('#oauth_client_grant_types').val('');
    $("input[name='oauth[client_grant_types][]']").prop("checked",false);
    $("input[name='oauth[client_scopes][]']").prop("checked",false);
}