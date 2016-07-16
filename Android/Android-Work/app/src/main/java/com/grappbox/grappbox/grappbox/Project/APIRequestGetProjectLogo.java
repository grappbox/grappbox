package com.grappbox.grappbox.grappbox.Project;

import android.content.ContentValues;
import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.AsyncTask;
import android.os.Environment;
import android.provider.MediaStore;
import android.util.Base64;
import android.util.DisplayMetrics;
import android.util.Log;
import android.widget.Toast;

import com.grappbox.grappbox.grappbox.Model.APIConnectAdapter;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.OutputStream;

/**
 * Created by tan_f on 15/07/2016.
 */
public class APIRequestGetProjectLogo extends AsyncTask<String, Void, String> {

    private final String PATH = "projects/getprojectlogo/";
    private Context _context;
    private String _projectName;

    public APIRequestGetProjectLogo(Context context, String projectName)
    {
        _context = context;
        _projectName = projectName;
    }

    @Override
    protected void onPostExecute(String result) {
        super.onPostExecute(result);
        if (result != null){
            try {
                JSONObject logo = new JSONObject(result).getJSONObject("data");
                String logoData = logo.getString("logo");
                if (!logoData.equals("null")){
                        try {
                            File file = new File(_context.getFilesDir(), "logo-" + _projectName + ".png");
                            byte[] blob = Base64.decode(logoData, Base64.DEFAULT);
                            BitmapFactory.Options opt = new BitmapFactory.Options();
                            DisplayMetrics metrics = _context.getResources().getDisplayMetrics();
                            opt.inScreenDensity = metrics.densityDpi;
                            opt.inTargetDensity = metrics.densityDpi;
                            opt.inDensity = DisplayMetrics.DENSITY_DEFAULT;
                            Bitmap image = BitmapFactory.decodeByteArray(blob, 0, blob.length, new BitmapFactory.Options());
                            if (image != null) {
                                OutputStream fOut = new FileOutputStream(file);
                                image.compress(Bitmap.CompressFormat.PNG, 85, fOut);
                                fOut.flush();
                                fOut.close();

                                MediaStore.Images.Media.insertImage(_context.getContentResolver(),file.getAbsolutePath(),file.getName(),file.getName());
                                Log.v("Logo", "saved");
                            }
                        } catch (IOException e){
                            e.printStackTrace();
                        }
                }
            } catch (JSONException e){
                e.printStackTrace();
            }
        }
    }

    @Override
    protected String doInBackground(String ... param)
    {
        Integer APIResponse;
        String resultAPI = null;

        try {
            APIConnectAdapter.getInstance().startConnection(PATH + SessionAdapter.getInstance().getToken() + "/" + param[0]);
            APIConnectAdapter.getInstance().setRequestConnection("GET");

            APIResponse = APIConnectAdapter.getInstance().getResponseCode();
            if (APIResponse == 200) {
                resultAPI = APIConnectAdapter.getInstance().getInputSream();
            }

        } catch (IOException e){
            e.printStackTrace();
            return null;
        } finally {
            APIConnectAdapter.getInstance().closeConnection();
        }
        return resultAPI;
    }
}