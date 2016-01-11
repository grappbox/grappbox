package com.grappbox.grappbox.grappbox;

import android.os.Bundle;
import android.preference.ListPreference;
import android.preference.PreferenceFragment;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Locale;

/**
 * Created by tan_f on 07/01/2016.
 */
public class UserProfilePreferenceFragment extends PreferenceFragment {

    @Override
    public void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.user_profile_preference);
        Locale[] locales = Locale.getAvailableLocales();
        ArrayList<String> countries = new ArrayList<String>();
        for (Locale locale : locales){
            String country = locale.getDisplayCountry();
            if (country.trim().length() > 0 && !countries.contains(country)){
                countries.add(country);
            }
        }
        Collections.sort(countries);

        CharSequence[] charCountries = countries.toArray(new CharSequence[countries.size()]);
        final ListPreference country = (ListPreference)findPreference("pref_country");
        country.setEntries(charCountries);
        country.setDefaultValue("France");
        country.setEntryValues(charCountries);
    }
}
