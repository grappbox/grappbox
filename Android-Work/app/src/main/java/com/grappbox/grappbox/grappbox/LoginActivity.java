package com.grappbox.grappbox.grappbox;

import android.app.DialogFragment;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

public class LoginActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
    }

    public void CheckLogin(View view)
    {
        EditText login = (EditText) findViewById(R.id.loginInput);
        EditText passw = (EditText) findViewById(R.id.passwInput);

        if (login.getText().toString().equals("toto") && passw.getText().toString().equals("tata"))
        {
            Intent intent = new Intent(this, DashboardActivity.class);
            startActivity(intent);
        }
        else
        {
            DialogFragment loginError = new LoginErrorAlertFragment();
            loginError.show(getFragmentManager(), "LoginError");
        }
    }
}
