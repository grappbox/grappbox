package com.grappbox.grappbox.grappbox.Login;

import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

public class LoginActivity extends AppCompatActivity {

    EditText    _login;
    EditText    _passw;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
    }

    public void LoginUser(View view)
    {
        _login = (EditText) findViewById(R.id.loginInput);
        _passw = (EditText) findViewById(R.id.passwInput);

        SessionAdapter.initializeInstance(this.getApplicationContext());
        APIRequestLogin api = new APIRequestLogin(this);
        api.execute(_login.getText().toString(), _passw.getText().toString());
    }
}
