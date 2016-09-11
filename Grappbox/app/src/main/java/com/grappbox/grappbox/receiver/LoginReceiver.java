package com.grappbox.grappbox.receiver;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.os.Bundle;
import android.os.Handler;
import android.support.v4.os.ResultReceiver;

import com.grappbox.grappbox.AddAccountActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.sync.GrappboxSyncAdapter;

import java.util.Calendar;

@SuppressLint("ParcelCreator")
public class LoginReceiver extends ResultReceiver {
    private Activity mContext;

    public LoginReceiver(Handler handler, Activity context) {
        super(handler);
        mContext = context;
    }

    @Override
    protected void onReceiveResult(int resultCode, Bundle resultData) {
        super.onReceiveResult(resultCode, resultData);
        if (mContext instanceof AddAccountActivity) {
            Bundle response = new Bundle();
            Bundle extraData = new Bundle();
            Calendar expiration = Calendar.getInstance();
            expiration.add(Calendar.DATE, 1);

            response.putString(AccountManager.KEY_ACCOUNT_NAME, resultData.getString(GrappboxJustInTimeService.EXTRA_MAIL));
            response.putString(AccountManager.KEY_ACCOUNT_TYPE, mContext.getString(R.string.sync_account_type));
            extraData.putString(GrappboxJustInTimeService.EXTRA_API_TOKEN, resultData.getString(GrappboxJustInTimeService.EXTRA_API_TOKEN));
            extraData.putString(Session.ACCOUNT_EXPIRATION_TOKEN, String.valueOf(expiration.getTimeInMillis()));

            AccountManager am = AccountManager.get(mContext);
            Account newAccount = new Account(resultData.getString(AccountManager.KEY_ACCOUNT_NAME), resultData.getString(AccountManager.KEY_ACCOUNT_TYPE));

            am.addAccountExplicitly(newAccount, resultData.getString(GrappboxJustInTimeService.EXTRA_CRYPTED_PASSWORD), extraData);
            GrappboxSyncAdapter.onAccountAdded(newAccount, mContext);
            ((AddAccountActivity) mContext).setResponse(resultData);
            mContext.finish();
        }
    }
}
