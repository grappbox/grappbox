package com.grappbox.grappbox.singleton;

import android.accounts.Account;

/**
 * Created by marcw on 03/09/2016.
 */
public class Session {
    private static Session ourInstance = new Session();
    public static final String ACCOUNT_EXPIRATION_TOKEN = "tokenExpiration";

    private Account currentAccount = null;

    private Session() {
    }

    public Account getCurrentAccount() { return currentAccount; }
    public void setCurrentAccount(Account account) { currentAccount = account; }
}
