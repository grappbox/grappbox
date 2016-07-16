package com.grappbox.grappbox.grappbox.Gantt;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by wieser_m on 22/04/2016.
 */
public class TaskInfoHandler {
    private static void DialogContactServer(Context context, int statusCode)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(context);

        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        builder.setNegativeButton(null, null);
        builder.setTitle(context.getString(R.string.str_error) + " " + String.valueOf(statusCode));
        builder.setMessage(R.string.problem_grappbox_server);
        builder.show();
    }

    private static void DialogInsufficentRight(Context context)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(context);

        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        builder.setNegativeButton(null, null);
        builder.setTitle(R.string.error_insuficent_right);
        builder.setMessage(R.string.error_ir_description);
        builder.show();
    }

    private static void DialogUnexpectedError(Context context, String code, String description)
    {
        AlertDialog.Builder builder = new AlertDialog.Builder(context);

        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.dismiss();
            }
        });
        builder.setNegativeButton(null, null);
        builder.setTitle(R.string.error_unexpected_error);
        builder.setMessage(context.getString(R.string.error_ue_description) + context.getString(R.string.error_code_head) + code);
        builder.show();
    }

    public static boolean process(Context context, int statusCode, JSONObject info)
    {
        if (statusCode >= 300 || info == null)
        {
            DialogContactServer(context, statusCode);
            return true;
        }
        try {
            String returnCode = info.getString("return_code");

            if (!returnCode.startsWith("1.") && !returnCode.endsWith(".7"))
            {
                if (returnCode.endsWith(".9"))
                    DialogInsufficentRight(context);
                else
                    DialogUnexpectedError(context, returnCode, info.getString("return_message"));
                return true;
            }
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return false;
    }
}
