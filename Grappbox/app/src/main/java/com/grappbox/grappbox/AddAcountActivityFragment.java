package com.grappbox.grappbox;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.support.design.widget.Snackbar;
import android.support.design.widget.TextInputLayout;
import android.support.v4.app.Fragment;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

import com.grappbox.grappbox.receiver.LoginReceiver;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

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
        final View v = inflater.inflate(R.layout.fragment_add_account, container, false);
        final Intent process = getActivity().getIntent();
        if (process == null)
            throw new IllegalArgumentException();
        mMail = (EditText) v.findViewById(R.id.input_login);
        mPassword = (EditText) v.findViewById(R.id.input_password);
        mTILMail = (TextInputLayout) v.findViewById(R.id.til_login);
        mTILPassword = (TextInputLayout) v.findViewById(R.id.til_password);
        mAddAccount = (Button) v.findViewById(R.id.btn_add_account);
        mReceiver = new LoginReceiver(new Handler(), getActivity());

        mPassword.setOnEditorActionListener(new TextView.OnEditorActionListener() {
            @Override
            public boolean onEditorAction(TextView textView, int i, KeyEvent keyEvent) {
                InputMethodManager imm = (InputMethodManager) getActivity().getSystemService(Context.INPUT_METHOD_SERVICE);

                mAddAccount.performClick();
                imm.hideSoftInputFromInputMethod(mPassword.getWindowToken(), InputMethodManager.RESULT_UNCHANGED_SHOWN);
                return true;
            }
        });

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
                    Snackbar.make(v, R.string.error_network_disconnected, Snackbar.LENGTH_LONG).show();
                    return;
                }
                Account newAccount = new Account(mMail.getText().toString(), getString(R.string.sync_account_type));
                AccountManager am = AccountManager.get(getActivity());

                if (am.getPassword(newAccount) != null){
                    //The account is already synchronized return error
                    Snackbar.make(v, R.string.error_account_already_registered, Snackbar.LENGTH_LONG);
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