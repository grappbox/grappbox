package com.grappbox.grappbox.receiver;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.os.Bundle;
import android.os.Handler;
import android.support.design.widget.Snackbar;
import android.support.v4.os.ResultReceiver;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

/**
 * Created by marcw on 15/09/2016.
 */
@SuppressLint("ParcelCreator")
public class ErrorReceiver extends ResultReceiver {
    private Activity mContext;

    public ErrorReceiver(Handler handler, Activity context) {
        super(handler);
        mContext = context;
    }

    @Override
    protected void onReceiveResult(int resultCode, Bundle resultData) {
        super.onReceiveResult(resultCode, resultData);
        String error = resultData.getString(GrappboxJustInTimeService.BUNDLE_KEY_ERROR_MSG);
        if (resultCode == Activity.RESULT_CANCELED && error != null){
            Snackbar.make(mContext.findViewById(R.id.fragment_container), error, Snackbar.LENGTH_LONG).show();
        }
    }
}
