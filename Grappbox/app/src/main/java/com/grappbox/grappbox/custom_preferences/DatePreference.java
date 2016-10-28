/*
 * Created by Marc Wieser on 28/10/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox.custom_preferences;

import android.app.AlertDialog;
import android.content.Context;
import android.preference.DialogPreference;
import android.util.AttributeSet;
import android.view.View;
import android.widget.DatePicker;


public class DatePreference extends DialogPreference {
    DatePicker picker;

    public DatePreference(Context context, AttributeSet attrs) {
        super(context, attrs);
        setDialogTitle("");
    }

    @Override
    protected View onCreateDialogView() {
        picker = new DatePicker(getContext());
        return picker;
    }

    @Override
    protected void onPrepareDialogBuilder(AlertDialog.Builder builder) {
        super.onPrepareDialogBuilder(builder);
    }
}
