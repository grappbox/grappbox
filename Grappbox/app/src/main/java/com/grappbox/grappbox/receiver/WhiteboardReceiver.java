package com.grappbox.grappbox.receiver;

import android.app.Activity;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.os.ResultReceiver;

import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;

import org.json.JSONArray;
import org.json.JSONException;

/**
 * Created by marcw on 02/12/2016.
 */

public class WhiteboardReceiver extends ResultReceiver {

    public interface Callbacks{
        void onReceivedObjects(JSONArray objects);
    }

    private Callbacks mListener;

    /**
     * Create a new ResultReceive to receive results.  Your
     * {@link #onReceiveResult} method will be called from the thread running
     * <var>handler</var> if given, or from an arbitrary thread if null.
     */
    public WhiteboardReceiver(Callbacks listener) {
        super(new Handler());
        mListener = listener;
    }

    @Override
    protected void onReceiveResult(int resultCode, Bundle resultData) {
        super.onReceiveResult(resultCode, resultData);
        if (resultCode == Activity.RESULT_OK){
            try {
                mListener.onReceivedObjects(new JSONArray(resultData.getString(GrappboxWhiteboardJIT.BUNDLE_JSON_OBJS)));
            } catch (JSONException e) {
                e.printStackTrace();
                mListener.onReceivedObjects(null);
            }
        }
    }
}
