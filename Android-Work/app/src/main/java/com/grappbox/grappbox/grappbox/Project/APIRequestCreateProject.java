package com.grappbox.grappbox.grappbox.Project;

import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

/**
 * Created by tan_f on 25/04/2016.
 */
public class APIRequestCreateProject extends AsyncTask<String, Void, String> {

    private final String PATH = "projects/projectcreation";
    private CreateProjectActivity _createProjectActivity;

    public APIRequestCreateProject(CreateProjectActivity activity)
    {
        _createProjectActivity = activity;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);

        if (result == null) {
            Toast.makeText(_createProjectActivity, "An error occur", Toast.LENGTH_SHORT).show();
            return;
        }

        _createProjectActivity.finish();
    }

    @Override
    protected String doInBackground(String ... param)
    {
        Integer APIResponse;
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(PATH);
            APIConnectAdapter.getInstance().setRequestConnection("POST");

            JSONObject JSONParam = new JSONObject();
            JSONObject JSONData = new JSONObject();

            String title = param[0];
            String safepassword = param[1];
            String desc = param[2];
            String phone = param[3];
            String company = param[4];
            String facebook = param[5];
            String twitter = param[6];
            String mail = param[7];
            String token = SessionAdapter.getInstance().getToken();
            JSONParam.put("name", title);
            JSONParam.put("token", token);
            JSONParam.put("description", desc);
            JSONParam.put("phone", phone);
            JSONParam.put("company", company);
            JSONParam.put("facebook", facebook);
            JSONParam.put("twitter", twitter);
            JSONParam.put("email", mail);
            JSONParam.put("password", safepassword);
            JSONData.put("data", JSONParam);
            APIConnectAdapter.getInstance().sendJSON(JSONData);
            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            if (APIResponse == 201) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            }

        } catch (IOException | JSONException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }
}
