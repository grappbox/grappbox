package com.grappbox.grappbox.receiver;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.os.ResultReceiver;
import android.support.v4.widget.SwipeRefreshLayout;

import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.json.JSONException;
import org.json.JSONObject;

@SuppressLint("ParcelCreator")
public class RefreshReceiver extends ResultReceiver {
    private SwipeRefreshLayout mRefresh;
    private Context mContext;

    public RefreshReceiver(Handler handler, SwipeRefreshLayout refreshLayout, Context context) {
        super(handler);
        mContext = context;
        mRefresh = refreshLayout;
    }

    @Override
    protected void onReceiveResult(int resultCode, Bundle resultData) {
        super.onReceiveResult(resultCode, resultData);
        mRefresh.setRefreshing(false);
        if (resultCode == Activity.RESULT_OK || resultData == null)
            return;
        try {
            JSONObject error = new JSONObject(resultData.getString(GrappboxJustInTimeService.BUNDLE_KEY_JSON));
            Utils.Errors.checkAPIErrorToDisplay(mContext, error);
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
}
