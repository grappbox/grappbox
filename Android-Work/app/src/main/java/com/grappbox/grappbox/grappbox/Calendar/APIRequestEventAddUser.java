package com.grappbox.grappbox.grappbox.Calendar;

import android.app.Dialog;
import android.content.ContentValues;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.List;
import java.util.Vector;

/**
 * Created by tan_f on 31/01/2016.
 */
public class APIRequestEventAddUser extends AsyncTask<String, Void, String> {

    private EventDetailFragment _context;
    private Dialog              _dialog;
    private int                 _idEvent;
    private int                 _idUser;

    APIRequestEventAddUser(EventDetailFragment context, int idEvent, Dialog dialog)
    {
        _context = context;
        _dialog = dialog;
        _idEvent = idEvent;
    }

    @Override
    protected void onPostExecute(String result)
    {
        super.onPostExecute(result);
        if (result != null) {
            _dialog.dismiss();
            CharSequence text = "Successful user add";
            Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
            APIRequestGetEventData refresh = new APIRequestGetEventData(_context, _idEvent);
            refresh.execute();
        } else {
            CharSequence text = "An Error Occured. Cannot add user to this event";
            Toast.makeText(_context.getContext(), text, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        String resultAPI;
        Integer APIResponse;
        Integer APISendRespond;

        try {
            String token = SessionAdapter.getInstance().getToken();
            APIConnectAdapter.getInstance().startConnection("user/getidbyemail/" + token + "/" + param[0], "V0.2");
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            Log.v("Response API :", APIResponse.toString());
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
                JSONObject forecastJSON = new JSONObject(resultAPI).getJSONObject("data");
                _idUser = forecastJSON.getInt("id");

                APIConnectAdapter.getInstance().startConnection("event/setparticipants", "V0.2");
                APIConnectAdapter.getInstance().setRequestConnection("PUT");
                JSONObject JSONData = new JSONObject();
                JSONObject JSONParam = new JSONObject();
                JSONArray ArrayToAdd = new JSONArray();
                JSONArray ArrayToRemove = new JSONArray();

                JSONParam.put("token", SessionAdapter.getInstance().getToken());
                JSONParam.put("eventId", _idEvent);
                ArrayToAdd.put(_idUser);
                JSONParam.put("toAdd", ArrayToAdd);
                JSONParam.put("toRemove", ArrayToRemove);
                JSONData.put("data", JSONParam);

                Log.v("Add USer JSON:", JSONData.toString());
                APIConnectAdapter.getInstance().sendJSON(JSONData);
                APISendRespond = APIConnectAdapter.getInstance().getResponseCode();
                if (APISendRespond == 200) {
                    resultAPI = APIConnectAdapter.getInstance().getInputSream();
                } else {
                    return null;
                }

                Log.v("Result API :", resultAPI);
            } else {
                return null;
            }

        } catch (IOException e){
            Log.e("APIConnection", "Error ", e);
            return null;
        } catch (JSONException j){
            Log.e("APIConnection", "Error ", j);
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }

}
