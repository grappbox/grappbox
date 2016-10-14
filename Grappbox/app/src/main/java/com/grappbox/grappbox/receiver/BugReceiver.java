package com.grappbox.grappbox.receiver;

import android.app.Activity;
import android.os.Bundle;
import android.os.Handler;
import android.support.design.widget.Snackbar;
import android.support.v4.os.ResultReceiver;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.BugModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by marc on 13/10/2016.
 */

public class BugReceiver extends ResultReceiver {
    private BugModel mModel;
    private List<Callback> callbacks;
    private Activity mContext;
    public static final String EXTRA_BUG_MODEL = "bug_model";


    public interface Callback{
        void onDataReceived(BugModel model);
    }

    public BugReceiver(Activity context, Handler handler) {
        super(handler);
        callbacks = new ArrayList<>();
        mContext = context;
    }

    @Override
    protected void onReceiveResult(int resultCode, Bundle resultData) {
        super.onReceiveResult(resultCode, resultData);
        String error = resultData != null && resultData.containsKey(GrappboxJustInTimeService.BUNDLE_KEY_ERROR_MSG) ? resultData.getString(GrappboxJustInTimeService.BUNDLE_KEY_ERROR_MSG) : null;
        if (resultCode == Activity.RESULT_CANCELED && error != null){
            Snackbar.make(mContext.findViewById(R.id.fragment_container), error, Snackbar.LENGTH_LONG).show();
            return;
        }
        mModel = resultData.getParcelable(EXTRA_BUG_MODEL);
        for (Callback callback : callbacks){
            callback.onDataReceived(mModel);
        }
    }

    public void registerCallback(Callback callback){
        this.callbacks.add(callback);
    }
}
