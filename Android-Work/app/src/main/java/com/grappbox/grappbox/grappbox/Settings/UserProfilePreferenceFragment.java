package com.grappbox.grappbox.grappbox.Settings;

import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.ContentValues;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.EditTextPreference;
import android.preference.ListPreference;
import android.preference.Preference;
import android.preference.PreferenceFragment;
import android.preference.PreferenceManager;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Locale;

public class UserProfilePreferenceFragment extends PreferenceFragment implements SharedPreferences.OnSharedPreferenceChangeListener {

    private ProgressDialog      _progress;
    private ArrayList<String>   _countries = new ArrayList<String>();
    private EditTextPreference  _firstName;
    private EditTextPreference  _lastName;
    private EditTextPreference  _phoneNumber;
    private EditTextPreference  _linkedinLink;
    private EditTextPreference  _viadeoLink;
    private EditTextPreference  _twitterLink;
    private EditTextPreference  _password;
    private ListPreference      _country;
    private boolean             _preferenceSet = false;
    private ContentValues       _preferenceKeyProfile = new ContentValues();

    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.user_profile_preference);

        _progress = new ProgressDialog(this.getActivity());
        _progress.setMessage(getString(R.string.progress_loading));
        _progress.setProgressStyle(ProgressDialog.STYLE_SPINNER);
        _progress.setIndeterminate(true);

        Locale[] locales = Locale.getAvailableLocales();
        for (Locale locale : locales){
            String country = locale.getDisplayCountry();
            if (country.trim().length() > 0 && !_countries.contains(country)){
                _countries.add(country);
            }
        }
        Collections.sort(_countries);

        CharSequence[] charCountries = _countries.toArray(new CharSequence[_countries.size()]);
        _firstName = (EditTextPreference)findPreference("pref_first_name");
        _lastName = (EditTextPreference)findPreference("pref_last_name");
        _phoneNumber = (EditTextPreference)findPreference("pref_phone_number_user");
        _linkedinLink = (EditTextPreference) findPreference("pref_linkedin");
        _viadeoLink = (EditTextPreference) findPreference("pref_viadeo");
        _twitterLink = (EditTextPreference) findPreference("pref_twitter");
        _country = (ListPreference)findPreference("pref_country");

        _country.setEntries(charCountries);
        for (int i = 0; i < charCountries.length; ++i){
            if (charCountries[i].equals("France"))
                _country.setValueIndex(i);
        }
        _country.setEntryValues(charCountries);
        _password = (EditTextPreference) findPreference("pref_password");
        _password.setDefaultValue(SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_PASSWORD));
        _password.setOnPreferenceClickListener(new Preference.OnPreferenceClickListener() {
            @Override
            public boolean onPreferenceClick(Preference preference) {
                AlertDialog.Builder confirmDialog = new AlertDialog.Builder(getActivity());
                confirmDialog.setTitle("New Password");
                LayoutInflater inflater = getActivity().getLayoutInflater();
                confirmDialog.setView(inflater.inflate(R.layout.dialog_change_password, null));
                confirmDialog.setPositiveButton(R.string.confirm_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });
                confirmDialog.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });
                confirmDialog.show();
                return true;
            }
        });
        _preferenceKeyProfile.put("pref_first_name", "firstname");
        _preferenceKeyProfile.put("pref_last_name", "lastname");
        _preferenceKeyProfile.put("pref_phone_number_user", "phone");
        _preferenceKeyProfile.put("pref_country", "country");
        _preferenceKeyProfile.put("pref_linkedin", "linkedin");
        _preferenceKeyProfile.put("pref_viadeo", "viadeo");
        _preferenceKeyProfile.put("pref_twitter", "twitter");
        _preferenceKeyProfile.put("pref_password", "password");

        _progress.show();
        APIRequestGetUserProfile api = new APIRequestGetUserProfile(this);
        api.execute();
    }

    @Override
    public void onResume()
    {
        super.onResume();
        getPreferenceManager().getSharedPreferences().registerOnSharedPreferenceChangeListener(this);
    }

    @Override
    public void onPause()
    {
        getPreferenceManager().getSharedPreferences().unregisterOnSharedPreferenceChangeListener(this);
        super.onPause();
    }

    @Override
    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key)
    {
        if (_preferenceSet){
            Log.v("Prefernce key", key);
            Log.v("Prefernce value", sharedPreferences.getString(key, null));
            if (key.equals("pref_password")) {
                AlertDialog.Builder confirmDialog = new AlertDialog.Builder(getActivity());
                confirmDialog.setTitle("Confirm your newPassword");
                confirmDialog.show();
            } else {
                _progress.show();
                APIRequestSetUserProfile api = new APIRequestSetUserProfile(this);
                api.execute(_preferenceKeyProfile.getAsString(key), sharedPreferences.getString(key, null));
            }

        }
    }

    public void setUserProfile(ContentValues userProfile)
    {
        SharedPreferences.Editor pref = PreferenceManager.getDefaultSharedPreferences(this.getActivity()).edit();
        _firstName.setText(userProfile.getAsString("firstname"));
        _firstName.setSummary(_firstName.getText());
        _lastName.setText(userProfile.getAsString("lastname"));
        _lastName.setSummary(_lastName.getText());
        _phoneNumber.setText(userProfile.getAsString("phone"));
        _phoneNumber.setSummary(_phoneNumber.getText());
        _linkedinLink.setText(userProfile.getAsString("linkedin"));
        _linkedinLink.setSummary(_linkedinLink.getText());

        _viadeoLink.setText(userProfile.getAsString("viadeo"));
        _viadeoLink.setSummary(_viadeoLink.getText());

        _twitterLink.setText(userProfile.getAsString("twitter"));
        _twitterLink.setSummary(_twitterLink.getText());

        CharSequence[] charCountries = _countries.toArray(new CharSequence[_countries.size()]);
        _country.setEntries(charCountries);
        for (int i = 0; i < charCountries.length; ++i){
            if (charCountries[i].equals(userProfile.getAsString("country")))
                _country.setValueIndex(i);
        }
        _country.setEntryValues(charCountries);
        _country.setSummary(_country.getValue());
        pref.commit();
        _preferenceSet = true;
        _progress.dismiss();
    }
}
