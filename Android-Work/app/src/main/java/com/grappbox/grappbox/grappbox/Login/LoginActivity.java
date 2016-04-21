package com.grappbox.grappbox.grappbox.Login;

import android.app.DialogFragment;
import android.app.ProgressDialog;
import android.content.Intent;
import android.content.res.Resources;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

public class LoginActivity extends AppCompatActivity {

    ProgressDialog _progress;
    EditText    _login;
    EditText    _passw;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
        _progress = new ProgressDialog(this);
        _progress.setMessage(getString(R.string.login_progress_label));
        _progress.setProgressStyle(ProgressDialog.STYLE_SPINNER);
        _progress.setIndeterminate(true);
        _login = (EditText) findViewById(R.id.loginInput);
        _passw = (EditText) findViewById(R.id.passwInput);
    }

    public void LoginUser(View view)
    {
        SessionAdapter.initializeInstance(this.getApplicationContext());
        APIRequestLogin api = new APIRequestLogin(this);
        api.execute(_login.getText().toString(), _passw.getText().toString());
        _progress.show();
    }

    public void loginSucced()
    {
        _progress.dismiss();
        Intent intent = new Intent(this, MainActivity.class);
        this.startActivity(intent);
    }

    public void loginFail()
    {
        _progress.dismiss();
        DialogFragment loginError = new LoginErrorAlertFragment();
        loginError.show(getFragmentManager(), "LoginError");
    }
}
