package com.grappbox.grappbox.grappbox.Project;

import android.app.AlertDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.content.res.Resources;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.os.Bundle;
import android.preference.EditTextPreference;
import android.preference.ListPreference;
import android.preference.Preference;
import android.preference.PreferenceFragment;
import android.preference.PreferenceManager;
import android.provider.MediaStore;
import android.support.v7.app.ActionBar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.util.Base64;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.ProjectSettingsActivity;
import com.grappbox.grappbox.grappbox.R;
import com.grappbox.grappbox.grappbox.Settings.APIRequestSetUserProfile;

import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.Objects;


/**
 * Created by tan_f on 14/07/2016.
 */
public class CreateProjectPreferenceActivity extends AppCompatActivity {

    private static CreateProjectPreferenceActivity _childrenParent;
    private static CreateProjectFragment _fragment;

    private static final String[] PreferenceKeys = {
            "project_title",
            "project_desc",
            "project_phone",
            "project_company",
            "project_mail",
            "project_facebook",
            "project_twitter",
            "project_logo",
            "project_safe_password",
            "confirm_creation"
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_create_project);
        Toolbar toolbar = (Toolbar) findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        _fragment = new CreateProjectFragment();
        getFragmentManager().beginTransaction().replace(R.id.content_project_creation, _fragment).commit();
        _childrenParent = this;
        setupActionBar();
    }

    private void setupActionBar() {
        ActionBar actionBar = getSupportActionBar();
        if (actionBar != null) {
            actionBar.setDisplayHomeAsUpEnabled(true);
        }
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == ProjectSettingsActivity.PICK_PNG_FROM_SYSTEM && resultCode == RESULT_OK && data != null) {
            Uri selectedImage = data.getData();
            try {
                Bitmap bitmap = MediaStore.Images.Media.getBitmap(this.getContentResolver(),selectedImage);
                _fragment.setLogo(bitmap);
            } catch (IOException e){
                e.printStackTrace();
            }

        }
    }

    static public class CreateProjectFragment extends PreferenceFragment implements Preference.OnPreferenceChangeListener {

        private String _logoValue = null;
        private EditTextPreference _title;
        private EditTextPreference _desc;
        private EditTextPreference _phone;
        private EditTextPreference _company;
        private EditTextPreference _mail;
        private EditTextPreference _facebook;
        private EditTextPreference _twitter;
        private EditTextPreference _password;

        public CreateProjectFragment()
        {

        }

        @Override
        public void onCreate(Bundle savedInstanceState) {
            super.onCreate(savedInstanceState);
            addPreferencesFromResource(R.xml.pref_project_creation);

            setHasOptionsMenu(true);
            _title = (EditTextPreference) findPreference("project_title");
            _desc = (EditTextPreference) findPreference("project_desc");
            _phone = (EditTextPreference) findPreference("project_phone");
            _company = (EditTextPreference) findPreference("project_company");
            _mail = (EditTextPreference) findPreference("project_mail");
            _facebook = (EditTextPreference) findPreference("project_facebook");
            _twitter = (EditTextPreference) findPreference("project_twitter");
            _password = (EditTextPreference) findPreference("project_safe_password");
            for (String prefKey : PreferenceKeys) {
                Preference pref = findPreference(prefKey);
                pref.getEditor().clear().commit();
                if (Objects.equals(prefKey, "project_logo")) {
                    pref.setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
                        @Override
                        public boolean onPreferenceClick(Preference preference) {
                            Intent intent = new Intent(Intent.ACTION_GET_CONTENT);
                            intent.setType("image/png");
                            intent.addCategory(Intent.CATEGORY_OPENABLE);
                            _childrenParent.startActivityForResult(intent, ProjectSettingsActivity.PICK_PNG_FROM_SYSTEM);
                            return false;
                        }
                    });
                } else if (Objects.equals(prefKey, "confirm_creation")) {
                    pref.setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
                        @Override
                        public boolean onPreferenceClick(Preference preference) {
                            CreateProject();
                            return false;
                        }
                    });
                } else  {
                    pref.setOnPreferenceChangeListener(this);
                }
            }
        }

        @Override
        public View onCreateView(LayoutInflater inflater, ViewGroup container,
                                 Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_create_project, container, false);
            return view;
        }

        public void setLogo(Bitmap bitmap)
        {
            Preference logo;

            logo = findPreference("project_logo");
            Drawable drawable = new BitmapDrawable(this.getResources(), bitmap);
            logo.setIcon(drawable);
            ByteArrayOutputStream byteArrayOutputStream  = new ByteArrayOutputStream ();
            bitmap.compress(Bitmap.CompressFormat.PNG, 100, byteArrayOutputStream );
            byte[] byteArray = byteArrayOutputStream .toByteArray();
            String encode = Base64.encodeToString(byteArray, Base64.DEFAULT);
            logo.setDefaultValue(encode);
            _logoValue = encode;
        }

        public boolean onPreferenceChange(Preference preference, Object newValue)
        {
            if (preference.getKey().equals("project_logo")) {

            } else {
                preference.setSummary(newValue.toString());
                preference.setDefaultValue(newValue.toString());
            }
            return true;
        }

        private void CreateProject()
        {
            String title = "";
            String password = "";
            String desc = "";
            String phone = "";
            String company = "";
            String facebook = "";
            String twitter = "";
            String mail = "";

            if (_title != null)
                title = _title.getText();
            if (_password != null)
                password = _password.getText();
            if (_desc != null)
                desc = _desc.getText();
            if (_phone != null)
                phone = _phone.getText();
            if (_company != null)
                company = _company.getText();
            if (_facebook != null)
                facebook = _facebook.getText();
            if (_twitter != null)
                twitter = _twitter.getText();
            if (_mail != null)
                mail = _mail.getText();

            if (title == null || password == null || title.equals("") || password.equals("")){
                AlertDialog.Builder builder = new AlertDialog.Builder(this.getActivity());
                builder.setTitle(R.string.project_create_alert_error);
                builder.setMessage(R.string.project_create_alert_error_message);
                builder.show();
                return ;
            }
            APIRequestCreateProject api = new APIRequestCreateProject(_childrenParent);
            api.execute(title, password, desc, phone, company, facebook, twitter, mail, _logoValue);
        }
    }
}
