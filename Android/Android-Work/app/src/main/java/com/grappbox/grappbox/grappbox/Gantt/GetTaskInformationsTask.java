package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Context;
import android.os.AsyncTask;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.text.ParseException;

/**
 * Created by wieser_m on 10/05/2016.
 */
public class GetTaskInformationsTask extends AsyncTask<String, Void, String> {
    APIConnectAdapter api;
    Context context;
    APIGetTaskInformationListener listener;

    public interface APIGetTaskInformationListener
    {
        public void onTaskFetched(Task task);
    }

    public GetTaskInformationsTask(Context context, APIGetTaskInformationListener listener) {
        this.context = context;
        this.listener = listener;
    }

    @Override
    protected String doInBackground(String... params) {
        if (params.length < 1)
            return null;
        String taskID = params[0];
        String token = SessionAdapter.getInstance().getToken();

        try {
            api = APIConnectAdapter.getInstance(true);
            api.setVersion("V0.2");
            api.startConnection("tasks/taskinformations/"+token+"/"+taskID);
            return api.getInputSream();
        } catch (IOException e) {
            e.printStackTrace();
        }

        return null;
    }

    @Override
    protected void onPostExecute(String s) {
        super.onPostExecute(s);
        Task taskFetched = null;
        if (s == null || s.isEmpty())
            return;
        try {
            JSONObject json = new JSONObject(s);
            JSONObject info = json.getJSONObject("info");
            if (TaskInfoHandler.process(context, api.getResponseCode(), info))
                return;
            JSONObject data = json.getJSONObject("data");
            taskFetched = new Task(data);
            if (listener != null && taskFetched != null)
                listener.onTaskFetched(taskFetched);
        } catch (JSONException | IOException | ParseException e) {
            e.printStackTrace();
        }
    }
}
