package com.grappbox.grappbox.grappbox.Settings;

import android.content.ContentValues;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.EditTextPreference;
import android.preference.ListPreference;
import android.preference.PreferenceFragment;
import android.preference.PreferenceManager;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Locale;

public class UserProfilePreferenceFragment extends PreferenceFragment {

    ArrayList<String> _countries = new ArrayList<String>();
    EditTextPreference _firstName;
    EditTextPreference _lastName;
    EditTextPreference _phoneNumber;
    EditTextPreference _linkedinLink;
    EditTextPreference _viadeoLink;
    EditTextPreference _twitterLink;
    ListPreference _country;

    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.user_profile_preference);
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
        APIRequestGetUserProfile api = new APIRequestGetUserProfile(this);
        api.execute();
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

        ListPreference country = (ListPreference)findPreference("pref_country");
        CharSequence[] charCountries = _countries.toArray(new CharSequence[_countries.size()]);
        country.setEntries(charCountries);
        for (int i = 0; i < charCountries.length; ++i){
            if (charCountries[i].equals(userProfile.getAsString("country")))
                country.setValueIndex(i);
        }
        country.setEntryValues(charCountries);
        pref.commit();
    }
}
