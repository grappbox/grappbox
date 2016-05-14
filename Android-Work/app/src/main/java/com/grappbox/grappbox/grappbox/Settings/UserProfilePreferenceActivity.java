package com.grappbox.grappbox.grappbox.Settings;

import android.os.Bundle;
import android.preference.PreferenceActivity;

import com.grappbox.grappbox.grappbox.R;

/**
 * Created by tan_f on 14/05/2016.
 */
public class UserProfilePreferenceActivity extends PreferenceActivity {

    @Override
    public void onCreate(Bundle savedInstanceState){
        super.onCreate(savedInstanceState);

        addPreferencesFromResource(R.xml.user_profile_preference);
    }

}
