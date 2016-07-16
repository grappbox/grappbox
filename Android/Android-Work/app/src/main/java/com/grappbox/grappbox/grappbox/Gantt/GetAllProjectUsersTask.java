package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.text.ParseException;
import java.util.ArrayList;

/**
 * Created by wieser_m on 14/05/2016.
 */
public class GetAllProjectUsersTask extends AsyncTask<String, Void, String> {
    APIConnectAdapter api;
    Context context;
    APIGetAllProjectUserListener listener;

    public interface APIGetAllProjectUserListener
    {
        public void onUsersFetched(ArrayList<TaskUser> users);
    }

    public GetAllProjectUsersTask(Context context, APIGetAllProjectUserListener listener) {
        this.context = context;
        this.listener = listener;
        api = APIConnectAdapter.getInstance(true);
    }

    @Override
    protected String doInBackground(String... params) {
        String token = SessionAdapter.getInstance().getToken();
        String projectId = String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject());

        try {
            api = APIConnectAdapter.getInstance(true);
            api.setVersion("V0.2");
            api.startConnection("projects/getusertoproject/"+token+"/"+projectId);
            return api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
        ArrayList<TaskUser> usersFetched = new ArrayList<>();
        if (s == null || s.isEmpty())
            return;
        try {
            JSONObject json = new JSONObject(s);
            JSONObject info = json.getJSONObject("info");
            if (TaskInfoHandler.process(context, api.getResponseCode(), info))
                return;
            JSONObject data = json.getJSONObject("data");
            JSONArray arr = data.getJSONArray("array");
            for (int i = 0; i < arr.length(); ++i)
                usersFetched.add(new TaskUser(arr.getJSONObject(i)));
            if (listener != null)
                listener.onUsersFetched(usersFetched);
        } catch (JSONException | IOException e) {
            e.printStackTrace();
        }
    }
}
