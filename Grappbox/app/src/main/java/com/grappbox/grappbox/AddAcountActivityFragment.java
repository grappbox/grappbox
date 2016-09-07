package com.grappbox.grappbox;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Handler;
import android.os.Parcel;
import android.support.design.widget.Snackbar;
import android.support.design.widget.TextInputLayout;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.support.v4.os.ResultReceiver;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;

import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;
import com.grappbox.grappbox.sync.GrappboxSyncAdapter;

import java.util.Calendar;

/**
 * A placeholder fragment containing a simple view.
 */
public class AddAcountActivityFragment extends Fragment {
    private EditText mMail, mPassword;
    private TextInputLayout mTILMail, mTILPassword;
    private Button mAddAccount;
    private LoginReceiver mReceiver;

    public AddAcountActivityFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_add_account, container, false);
        final Intent process = getActivity().getIntent();
        if (process == null)
            throw new IllegalArgumentException();
        mMail = (EditText) v.findViewById(R.id.input_login);
        mPassword = (EditText) v.findViewById(R.id.input_password);
        mTILMail = (TextInputLayout) v.findViewById(R.id.til_login);
        mTILPassword = (TextInputLayout) v.findViewById(R.id.til_password);
        mAddAccount = (Button) v.findViewById(R.id.btn_add_account);
        mReceiver = new LoginReceiver(new Handler(), getActivity());

        mAddAccount.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                boolean hasError = false;

                if (mMail.getText().toString().isEmpty())
                {
                    hasError = true;
                    mTILMail.setError(getActivity().getString(R.string.error_mail_not_filled));
                }
                if (mPassword.getText().toString().isEmpty())
                {
                    hasError = true;
                    mTILPassword.setError(getActivity().getString(R.string.error_password_not_filled));
                }
                if (hasError)
                    return;
                if (!Utils.Network.haveInternetConnection(getActivity()))
                {
                    if (getParentFragment() != null && getParentFragment().getView() != null)
                        Snackbar.make(getParentFragment().getView(), R.string.error_network_disconnected, Snackbar.LENGTH_LONG).show();
                    return;
                }
                Account newAccount = new Account(mMail.getText().toString(), getString(R.string.sync_account_type));
                AccountManager am = AccountManager.get(getActivity());

                if (am.getPassword(newAccount) != null){
                    //The account is already synchronized return error
                    if (getParentFragment() != null && getParentFragment().getView() != null)
                        Snackbar.make(getParentFragment().getView(), R.string.error_account_already_registered, Snackbar.LENGTH_LONG);
                    return;
                }

                Intent launchLoginService = new Intent(getActivity(), GrappboxJustInTimeService.class);
                launchLoginService.setAction(GrappboxJustInTimeService.ACTION_LOGIN);
                launchLoginService.putExtra(GrappboxJustInTimeService.EXTRA_MAIL, mMail.getText().toString());
                launchLoginService.putExtra(GrappboxJustInTimeService.EXTRA_CRYPTED_PASSWORD, Utils.Security.cryptString(mPassword.getText().toString()));
                launchLoginService.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mReceiver);
                getActivity().startService(launchLoginService);
            }
        });
        return v;
    }


}

@SuppressLint("ParcelCreator")
class LoginReceiver extends ResultReceiver{
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
