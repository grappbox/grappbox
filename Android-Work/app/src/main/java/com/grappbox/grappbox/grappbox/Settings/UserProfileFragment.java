package com.grappbox.grappbox.grappbox.Settings;

import android.app.Dialog;
import android.content.ContentValues;
import android.content.res.Resources;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Debug;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.GridView;
import android.widget.Spinner;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Locale;
import java.util.TimeZone;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class UserProfileFragment extends Fragment {

    private View _view;
    private Spinner _country;
    private Button _ChangePassword;
    private Button _SendModification;
    private EditText _FirstName;
    private EditText _LastName;
    private EditText _Mail;
    private EditText _PhoneNumber;
    private EditText _LinkedinLink;
    private EditText _TwitterLink;
    private EditText _ViadeoLink;

    public UserProfileFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_user_profile, container, false);

        _country = (Spinner)_view.findViewById(R.id.country_profile);
        Locale[] locales = Locale.getAvailableLocales();
        ArrayList<String> countries = new ArrayList<String>();
        for (Locale locale : locales){
            String country = locale.getDisplayCountry();
            if (country.trim().length() > 0 && !countries.contains(country)){
                countries.add(country);
            }
        }
        Collections.sort(countries);
        ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(this.getActivity(), R.layout.support_simple_spinner_dropdown_item, countries);
        _country.setAdapter(dataAdapter);
        _country.setSelection(37);
        _ChangePassword = (Button)_view.findViewById(R.id.change_password_button);
        _ChangePassword.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                final Dialog changePasswordDialog = new Dialog(getActivity());
                changePasswordDialog.setTitle("Set new password : ");
                changePasswordDialog.setContentView(R.layout.dialog_change_password);
                final EditText initPass = (EditText)changePasswordDialog.findViewById(R.id.actual_pass);
                final EditText newPass = (EditText)changePasswordDialog.findViewById(R.id.first_new_password);
                final EditText retypeNewPass = (EditText)changePasswordDialog.findViewById(R.id.retype_password);
                Button confirmChangePass = (Button)changePasswordDialog.findViewById(R.id.confirm_change_password);
                confirmChangePass.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        if (initPass.getText().toString().equals(SessionAdapter.getInstance().getPassword()) &&
                                newPass.getText().toString().equals(retypeNewPass.getText().toString()))
                        {
                            APIRequestChangePassword changePass = new APIRequestChangePassword();
                            changePass.execute(newPass.getText().toString());
                            changePasswordDialog.dismiss();
                        }
                    }
                });
                Button cancelChangePass = (Button)changePasswordDialog.findViewById(R.id.cancel_change_password);
                cancelChangePass.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        initPass.setText("");
                        newPass.setText("");
                        retypeNewPass.setText("");
                        changePasswordDialog.dismiss();
                    }
                });
                changePasswordDialog.show();
            }
        });
        _SendModification = (Button)_view.findViewById(R.id.send_modification_button);
        _FirstName = (EditText)_view.findViewById(R.id.first_name_profile);
        _LastName = (EditText)_view.findViewById(R.id.last_name_profile);
        _Mail = (EditText)_view.findViewById(R.id.email_profile);
        _PhoneNumber = (EditText)_view.findViewById(R.id.phone_number_profile);
        _LinkedinLink = (EditText)_view.findViewById(R.id.linkedin_profile);
        _ViadeoLink = (EditText)_view.findViewById(R.id.viadeo_profile);
        _TwitterLink = (EditText)_view.findViewById(R.id.twitter_profile);
        _SendModification.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                APIRequestSendUserProfile sender = new APIRequestSendUserProfile();
                sender.execute(_FirstName.getText().toString(), _LastName.getText().toString(), _Mail.getText().toString(), _PhoneNumber.getText().toString(), _LinkedinLink.getText().toString()
                        , _ViadeoLink.getText().toString(), _TwitterLink.getText().toString(), _country.getSelectedItem().toString());
            }
        });

        APIRequestUserProfile requestUserProfile = new APIRequestUserProfile();
        requestUserProfile.execute();
        return _view;
    }

    public void fillValues(ContentValues values)
    {
        if (values.get("first_name").toString().equals("null"))
            _FirstName.setText(getString(R.string.data_not_specified));
        else
            _FirstName.setText(values.get("first_name").toString());

        if (values.get("last_name").toString().equals("null"))
            _LastName.setText(getString(R.string.data_not_specified));
        else
            _LastName.setText(values.get("last_name").toString());

        if (values.get("email").toString().equals("null"))
            _Mail.setText(getString(R.string.data_not_specified));
        else
            _Mail.setText(values.get("email").toString());

        if (values.get("phone").toString().equals("null"))
            _PhoneNumber.setText(getString(R.string.data_not_specified));
        else
            _PhoneNumber.setText(values.get("phone").toString());

        if (values.get("linkedin").toString().equals("null"))
            _LinkedinLink.setText(getString(R.string.data_not_specified));
        else
            _LinkedinLink.setText(values.get("linkedin").toString());

        if (values.get("viadeo").toString().equals("null"))
            _ViadeoLink.setText(getString(R.string.data_not_specified));
        else
            _ViadeoLink.setText(values.get("viadeo").toString());

        if (values.get("twitter").toString().equals("null"))
            _TwitterLink.setText(getString(R.string.data_not_specified));
        else
            _TwitterLink.setText(values.get("twitter").toString());

        if (!values.get("country").toString().equals("null")) {
            Locale[] locales = Locale.getAvailableLocales();
            ArrayList<String> countries = new ArrayList<String>();

            for (Locale locale : locales) {
                String country = locale.getDisplayCountry();

                if (country.trim().length() > 0 && !countries.contains(country)) {
                    countries.add(country);
                }
            }
            Collections.sort(countries);
            ArrayAdapter<String> dataAdapter = new ArrayAdapter<String>(this.getActivity(), R.layout.support_simple_spinner_dropdown_item, countries);
            int i = 0;
            _country.setAdapter(dataAdapter);
            for (String country : countries)
            {
                if (values.get("country").toString().equals(country))
                    _country.setSelection(i);
                ++i;
            }


        }
    }

    public void UpdateResponse(String response)
    {
        Toast.makeText(getContext(), response, Toast.LENGTH_SHORT).show();
    }

    public static boolean isEmailValid(String email) {
        boolean isValid = false;

        String expression = "^[\\w\\.-]+@([\\w\\-]+\\.)+[A-Z]{2,4}$";
        CharSequence inputStr = email;

        Pattern pattern = Pattern.compile(expression, Pattern.CASE_INSENSITIVE);
        Matcher matcher = pattern.matcher(inputStr);
        if (matcher.matches()) {
            isValid = true;
        }
        return isValid;
    }

    public class APIRequestChangePassword extends AsyncTask<String, Void, Integer> {

        @Override
        protected void onPostExecute(Integer response) {
            super.onPostExecute(response);
            String answer;
            if (response == 200)
                answer = "Information Update";
            else
                answer = "Error in update";
            Log.v("result API", "DONE");
            UpdateResponse(answer);
        }

        @Override
        protected Integer doInBackground(String ... param)
        {
            Integer APIResponse;

            try {
                APIConnectAdapter.getInstance().startConnection("user/basicinformations/" + SessionAdapter.getInstance().getToken());
                APIConnectAdapter.getInstance().setRequestConnection("PUT");

                JSONObject JSONParam = new JSONObject();
                JSONParam.put("token", SessionAdapter.getInstance().getToken());
                JSONParam.put("password", param[0]);
                Log.v("JSON", JSONParam.toString());

                APIConnectAdapter.getInstance().sendJSON(JSONParam);
                APIResponse = APIConnectAdapter.getInstance().getResponseCode();

            } catch (IOException | JSONException e){
                Log.e("APIConnection", "Error ", e);
                return -1;
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return APIResponse;
        }

    }

    public class APIRequestSendUserProfile extends AsyncTask<String, Void, Integer> {

        @Override
        protected void onPostExecute(Integer response) {
            super.onPostExecute(response);

            String answer;
            if (response == 200)
                answer = "Information Update";
            else
                answer = "Error in update";
            Log.v("result API", "DONE");
            APIRequestUserProfile getProfile = new APIRequestUserProfile();
            getProfile.execute();
            UpdateResponse(answer);
        }

        @Override
        protected Integer doInBackground(String ... param)
        {
            Integer APIResponse;

            try {
                APIConnectAdapter.getInstance().startConnection("user/basicinformations/" + SessionAdapter.getInstance().getToken(), "V0.2");
                APIConnectAdapter.getInstance().setRequestConnection("PUT");

                JSONObject JSONParam = new JSONObject();
                JSONObject JSONData = new JSONObject();

                JSONParam.put("token", SessionAdapter.getInstance().getToken());
                JSONParam.put("firstname", param[0]);
                JSONParam.put("lastname", param[1]);
                if (!SessionAdapter.getInstance().getLogin().equals(param[2]) && isEmailValid(param[2]))
                    JSONParam.put("email", param[2]);
                JSONParam.put("phone", param[3]);
                JSONParam.put("country", param[7]);
                JSONParam.put("linkedin", param[4]);
                JSONParam.put("viadeo", param[5]);
                JSONParam.put("twitter", param[6]);

//                JSONParam.put("birthday", "");
                JSONData.put("data", JSONParam);
                Log.v("JSON", JSONData.toString());

                APIConnectAdapter.getInstance().sendJSON(JSONData);

                APIResponse = APIConnectAdapter.getInstance().getResponseCode();
                String toto;
                if (APIResponse == 200)
                    toto = APIConnectAdapter.getInstance().getInputSream();

            } catch (IOException | JSONException e){
                e.printStackTrace();
                return -1;
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return APIResponse;
        }

    }

    public class APIRequestUserProfile extends AsyncTask<String, Void, ContentValues> {

        @Override
        protected void onPostExecute(ContentValues result) {
            super.onPostExecute(result);
            if (result != null)
                fillValues(result);
        }

        @Override
        protected ContentValues doInBackground(String ... param)
        {
            ContentValues contentAPI = null;
            Integer responseAPI;
            String resultAPI;

            try {
                APIConnectAdapter.getInstance().startConnection("user/getuserbasicinformations/" + SessionAdapter.getInstance().getToken() + "/" +String.valueOf(SessionAdapter.getInstance().getUserID()));
                APIConnectAdapter.getInstance().setRequestConnection("GET");

                responseAPI = APIConnectAdapter.getInstance().getResponseCode();
                Log.v("User result", responseAPI.toString());
                if (responseAPI == 200) {
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                    contentAPI = APIConnectAdapter.getInstance().getUserInformation(resultAPI);
                }

            } catch (IOException | JSONException e){
                e.printStackTrace();
                return null;
            } finally {
                APIConnectAdapter.getInstance().closeConnection();
            }
            return contentAPI;
        }

    }
}
