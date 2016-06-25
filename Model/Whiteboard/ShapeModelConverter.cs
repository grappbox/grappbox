using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using GrappBox.ViewModel;
using GrappBox.Model;
using GrappBox.Model.Whiteboard;
using Windows.UI.Xaml.Media;
using Windows.UI;
using Windows.Foundation;

namespace GrappBox.Model.Whiteboard
{
    class ShapeModelConverter
    {
        public static Color FromhexaToColor(string hex)
        {
            Color color = new Color();
            hex = hex.Substring(1);
            color.R = byte.Parse(hex.Remove(2), System.Globalization.NumberStyles.HexNumber);
            hex = hex.Substring(2);
            color.G = byte.Parse(hex.Remove(2), System.Globalization.NumberStyles.HexNumber);
            hex = hex.Substring(2);
            color.G = byte.Parse(hex, System.Globalization.NumberStyles.HexNumber);
            return color;
        }

        public static ShapeControler ModelToShape(ObjectModel om, int objectId)
        {
            if (om.Type == "RECTANGLE")
                return jsonToRectangle(om, objectId);
            return null;
        }
        private static ShapeControler jsonToRectangle(ObjectModel om, int objectId)
        {
            SolidColorBrush fillColor = new SolidColorBrush(FromhexaToColor(om.Background));
            SolidColorBrush strokeColor = new SolidColorBrush(FromhexaToColor(om.Color));
            Point p = new Point(om.PositionStart.X, om.PositionStart.Y);
            double thickness = double.Parse(om.LineWeight);
            ShapeControler sc = new ShapeControler(WhiteboardTool.RECTANGLE, p, strokeColor, fillColor, thickness);
            sc.Id = objectId;
            p = new Point(om.PositionEnd.X, om.PositionEnd.Y);
            sc.Update(p);
            return sc;
        }
    }
}
