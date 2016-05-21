package com.grappbox.grappbox.grappbox.Settings;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 21/05/2016.
 */
public class APIRequestSetUserProfile extends AsyncTask<String, Void, String> {

    private final static String _PATH = "user/basicinformations/";
    private Integer _APIResponse;
    private UserProfilePreferenceFragment _context;

    APIRequestSetUserProfile(UserProfilePreferenceFragment context)
    {
        _context = context;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (result == null || _APIResponse != 200) {
            APIRequestGetUserProfile api = new APIRequestGetUserProfile(_context);
            api.execute();
            return ;
        }
        try {
            ContentValues profileInfo = new ContentValues();
            JSONObject jsonObject = new JSONObject(result).getJSONObject("data");

            profileInfo.put("firstname", jsonObject.getString("firstname"));
            profileInfo.put("lastname", jsonObject.getString("lastname"));
            profileInfo.put("birthday", jsonObject.getString("birthday"));
            profileInfo.put("avatar", jsonObject.getString("avatar"));
            profileInfo.put("email", jsonObject.getString("email"));
            profileInfo.put("phone", jsonObject.getString("phone"));
            profileInfo.put("country", jsonObject.getString("country"));
            profileInfo.put("linkedin", jsonObject.getString("linkedin"));
            profileInfo.put("viadeo", jsonObject.getString("viadeo"));
            profileInfo.put("twitter", jsonObject.getString("twitter"));
            APIConnectAdapter.getInstance().printContentValues(profileInfo);
            _context.setUserProfile(profileInfo);
        } catch (JSONException e){
            e.printStackTrace();
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI = null;
        if (param[0] == null || param[1] == null)
            return null;
        try {
            APIConnectAdapter.getInstance().startConnection(_PATH + SessionAdapter.getInstance().getUserData(SessionAdapter.KEY_TOKEN));
            APIConnectAdapter.getInstance().setRequestConnection("PUT");

            JSONObject JSONData = new JSONObject();
            JSONObject JSONParam = new JSONObject();
            JSONParam.put(param[0], param[1]);
            JSONData.put("data", JSONParam);

            APIConnectAdapter.getInstance().sendJSON(JSONData);
            _APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("JSON Response", String.valueOf(_APIResponse));
            if (_APIResponse == 200 || _APIResponse == 206)
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            Log.v("JSON Content", resultAPI);
        } catch (IOException | JSONException e){
            e.printStackTrace();
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }
}