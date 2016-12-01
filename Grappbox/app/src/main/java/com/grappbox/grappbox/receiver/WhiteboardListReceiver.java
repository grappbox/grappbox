package com.grappbox.grappbox.receiver;


import android.app.Activity;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.os.ResultReceiver;

import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;

import java.util.List;

public class WhiteboardListReceiver extends ResultReceiver {
    public interface Callback{
        void onListReceived(List<WhiteboardModel> models);
    }

    private Callback mCallback;

    /**
     * Create a new ResultReceive to receive results.  Your
     * {@link #onReceiveResult} method will be called from the thread running
     * <var>handler</var> if given, or from an arbitrary thread if null.
     *
     */
    public WhiteboardListReceiver(Callback callback) {
        super(new Handler());
        mCallback = callback;
    }


    @Override
    protected void onReceiveResult(int resultCode, Bundle resultData) {
        super.onReceiveResult(resultCode, resultData);
        if (resultCode == Activity.RESULT_OK){
            mCallback.onListReceived(resultData.<WhiteboardModel>getParcelableArrayList(GrappboxWhiteboardJIT.BUNDLE_PARCELABLE_ARRAY));
            return;
        }
        mCallback.onListReceived(null);
    }
}
