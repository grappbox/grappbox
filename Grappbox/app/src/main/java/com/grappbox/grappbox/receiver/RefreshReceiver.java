package com.grappbox.grappbox.receiver;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.os.Handler;
import android.support.design.widget.Snackbar;
import android.support.v4.app.FragmentActivity;
import android.support.v4.os.ResultReceiver;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.Utils;
import com.grappbox.grappbox.project_fragments.CloudFragment;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import org.json.JSONException;
import org.json.JSONObject;

@SuppressLint("ParcelCreator")
public class RefreshReceiver extends ResultReceiver {
    private SwipeRefreshLayout mRefresh;
    private FragmentActivity mContext;

    public RefreshReceiver(Handler handler, SwipeRefreshLayout refreshLayout, FragmentActivity context) {
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
        Snackbar.make(mContext.findViewById(R.id.fragment_container), resultData.getString(GrappboxJustInTimeService.BUNDLE_KEY_ERROR_MSG), Snackbar.LENGTH_LONG).show();
        if (resultData.getInt(GrappboxJustInTimeService.BUNDLE_KEY_ERROR_TYPE) == 9)
        {
            mContext.getSharedPreferences(CloudFragment.CLOUD_SHARED_PREF, Context.MODE_PRIVATE).edit().remove(CloudFragment.CLOUD_PREF_SAFE_BASE_KEY + mContext.getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1)).apply();
            mContext.getSupportFragmentManager().popBackStack();

        }
    }
}
