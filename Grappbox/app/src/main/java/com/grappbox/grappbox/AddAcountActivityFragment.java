package com.grappbox.grappbox;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.Intent;
import android.os.Handler;
import android.support.design.widget.Snackbar;
import android.support.design.widget.TextInputLayout;
import android.support.v4.app.Fragment;
import android.os.Bundle;
import android.support.v4.os.ResultReceiver;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;

import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

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
        mTILMail = (TextInputLayout) mMail.getParent();
        mTILPassword = (TextInputLayout) mPassword.getParent();
        mAddAccount = (Button) v.findViewById(R.id.btn_add_account);

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
                launchLoginService.putExtra(GrappboxJustInTimeService.EXTRA_CRYPTED_PASSWORD, Utils.Security.cryptString(mMail.getText().toString()));
                launchLoginService.putExtra(GrappboxJustInTimeService.EXTRA_RESPONSE_RECEIVER, mReceiver);
                getActivity().startService(launchLoginService);
            }
        });
        return v;
    }

    public class LoginReceiver extends ResultReceiver{
        public Creator<ResultReceiver> CREATOR;

        public LoginReceiver(Handler handler) {
            super(handler);
        }

        @Override
        protected void onReceiveResult(int resultCode, Bundle resultData) {
            super.onReceiveResult(resultCode, resultData);
            if (getParentFragment().getActivity() instanceof AddAccountActivity) {
                Bundle response = new Bundle();
                Bundle extraData = new Bundle();
                Calendar expiration = Calendar.getInstance();
                expiration.add(Calendar.DATE, 1);

                response.putString(AccountManager.KEY_ACCOUNT_NAME, resultData.getString(GrappboxJustInTimeService.EXTRA_MAIL));
                response.putString(AccountManager.KEY_ACCOUNT_TYPE, getString(R.string.sync_account_type));
                extraData.putString(GrappboxJustInTimeService.EXTRA_API_TOKEN, resultData.getString(GrappboxJustInTimeService.EXTRA_API_TOKEN));
                extraData.putString(Session.ACCOUNT_EXPIRATION_TOKEN, String.valueOf(expiration.getTimeInMillis()));

                AccountManager am = AccountManager.get(getParentFragment().getActivity());
                Account newAccount = new Account(resultData.getString(GrappboxJustInTimeService.EXTRA_MAIL), getString(R.string.sync_account_type));

                am.addAccountExplicitly(newAccount, resultData.getString(GrappboxJustInTimeService.EXTRA_CRYPTED_PASSWORD), extraData);

                ((AddAccountActivity) getParentFragment().getActivity()).setResponse(resultData);
                getParentFragment().getActivity().finish();
            }
        }
    }
}
