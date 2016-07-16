package com.grappbox.grappbox.grappbox.Login;

import android.app.DialogFragment;
import android.app.ProgressDialog;
import android.content.Intent;
import android.content.pm.PackageInstaller;
import android.content.res.Resources;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ProgressBar;
import android.widget.ScrollView;

import com.grappbox.grappbox.grappbox.MainActivity;
import com.grappbox.grappbox.grappbox.Model.LoadingActivity;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

public class LoginActivity extends LoadingActivity {

    private View _view;
    private EditText    _login;
    private EditText    _passw;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        _view = findViewById(R.id.frame_view);
        _login = (EditText) findViewById(R.id.loginInput);
        _passw = (EditText) findViewById(R.id.passwInput);
        SessionAdapter.initializeInstance(this.getApplicationContext());
        _login.setText(SessionAdapter.getInstance().getLogin());
        _passw.setText(SessionAdapter.getInstance().getPassword());
    }

    public void LoginUser(View view)
    {
        APIRequestLogin api = new APIRequestLogin(this);
        api.execute(_login.getText().toString(), _passw.getText().toString());
        startLoading(R.id.loader, _view);
    }

    public void loginSucced()
    {
        endLoading();
        Intent intent = new Intent(this, MainActivity.class);
        this.startActivity(intent);
    }

    public void loginFail()
    {
        endLoading();
        DialogFragment loginError = new LoginErrorAlertFragment();
        loginError.show(getFragmentManager(), "LoginError");
    }
}
