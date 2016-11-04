/*
 * Created by Marc Wieser on 4/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.custom_preferences;


import android.app.AlertDialog;
import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.preference.DialogPreference;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.CustomerAccessModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

public class CustomerAccessPreference extends DialogPreference {
    private CustomerAccessModel mModel;

    public CustomerAccessPreference(Context context, CustomerAccessModel model) {
        super(context, null);
        mModel = model;
        setTitle(mModel.name);
        setSummary(mModel.token);
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
        builder.setTitle(mModel.name);
        builder.setItems(R.array.customer_access_dialog_choice_entry, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                if (which == 0){
                    ((ClipboardManager) getContext().getSystemService(Context.CLIPBOARD_SERVICE)).setPrimaryClip(ClipData.newPlainText(mModel.name, mModel.token));
                } else {
                    Intent delete = new Intent(getContext(), GrappboxJustInTimeService.class);
                    delete.setAction(GrappboxJustInTimeService.ACTION_DELETE_CUTOMER_ACCESS);
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, mModel.projectId);
                    delete.putExtra(GrappboxJustInTimeService.EXTRA_CUSTOMER_ACCESS_ID, mModel._id);

                    getContext().startService(delete);
                }
                dialog.dismiss();
            }
        });
    }
}
