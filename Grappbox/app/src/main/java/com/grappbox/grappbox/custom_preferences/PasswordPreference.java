/*
 * Created by Marc Wieser on 29/10/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.custom_preferences;


import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.os.Build;
import android.preference.DialogPreference;
import android.util.AttributeSet;
import android.util.Pair;
import android.view.LayoutInflater;
import android.widget.EditText;

import com.grappbox.grappbox.R;

public class PasswordPreference extends DialogPreference {
    public PasswordPreference(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            builder.setView(R.layout.dialog_password_settings);
        } else {
            builder.setView(LayoutInflater.from(getContext()).inflate(R.layout.dialog_password_settings, null));
        }
        builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                String oldPass = ((EditText) ((Dialog)dialog).findViewById(R.id.old_password)).getText().toString();
                String newPass =  ((EditText) ((Dialog)dialog).findViewById(R.id.new_password)).getText().toString();
                callChangeListener(new Pair<String,String>(oldPass, newPass));
            }
        });
        builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                dialog.cancel();
            }
        });
    }

}
