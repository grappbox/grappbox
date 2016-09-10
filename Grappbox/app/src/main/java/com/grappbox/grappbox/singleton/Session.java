package com.grappbox.grappbox.singleton;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.Context;

import com.grappbox.grappbox.R;

/**
 * Created by marcw on 03/09/2016.
 */
public class Session {
    private static Session ourInstance = null;
    public static final String ACCOUNT_EXPIRATION_TOKEN = "tokenExpiration";

    public static Session getInstance(Context context) {
        if (ourInstance == null)
            ourInstance = new Session(context);
        return ourInstance;
    }

    private Account currentAccount = null;

    private Session(Context context) {
        currentAccount = AccountManager.get(context).getAccountsByType(context.getString(R.string.sync_account_type))[0];
    }

    public Account getCurrentAccount() { return currentAccount; }
    public void setCurrentAccount(Account account) { currentAccount = account; }
}
