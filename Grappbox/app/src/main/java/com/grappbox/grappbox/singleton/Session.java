package com.grappbox.grappbox.singleton;

import android.accounts.Account;
import android.accounts.AccountManager;
import android.content.Context;
import android.database.Cursor;

import com.grappbox.grappbox.R;

/**
 * Created by marcw on 03/09/2016.
 */
public class Session {
    private static Session ourInstance = null;

    public static final String ACCOUNT_EXPIRATION_TOKEN = "tokenExpiration";

    public static Session getInstance(Context context) {
        if (ourInstance == null)
            ourInstance = new Session();
        if (ourInstance.getCurrentAccount() == null)
            ourInstance.tryToInitAccount(context);
        return ourInstance;
    }

    private Account currentAccount = null;
    private long currentProject = -1;

    private void tryToInitAccount(Context context) throws SecurityException{
        Account[] accounts = AccountManager.get(context).getAccountsByType(context.getString(R.string.sync_account_type));
        if (accounts.length == 0)
            currentAccount = null;
        else
            currentAccount = accounts[0];
    }

    private Session() {

    }

    public Account getCurrentAccount() { return currentAccount; }
    public void setCurrentAccount(Account account) { currentAccount = account; }

    public long getSelectedProject() { return currentProject; }
    public void setSelectedProject(long project) { currentProject = project; }
}
