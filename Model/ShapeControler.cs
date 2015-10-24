using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.Foundation;
using Windows.UI;
using Windows.UI.Xaml.Media;
using Windows.UI.Xaml.Shapes;

namespace GrappBox
{
    enum shapeType { RECTANGLE, CIRCLE, LINE };
    class ShapeControler
    {
        private shapeType _type;
        public shapeType Type
        {
            get { return _type; }
            set { _type = value; }
        }
        private Shape _shape;
        public Shape BaseShape
        {
            get { return _shape; }
            set { _shape = value; }
        }
        private Point _pos;
        public Point Pos
        {
            get { return _pos; }
            set { _pos = value; }
        }
        public ShapeControler(Point pos, shapeType type)
        {
            Type = type;
            switch (type)
            {
                case shapeType.RECTANGLE:
                    var rect = new Rectangle();
                    rect.Width = 50;
                    rect.Height = 30;
                    rect.Stroke = new SolidColorBrush(Colors.Black);
                    rect.Fill = new SolidColorBrush(Colors.LightGray);
                    pos.X = pos.X - rect.Width / 2;
                    pos.Y = pos.Y - rect.Height / 2;
                    BaseShape = rect;
                    break;

                case shapeType.CIRCLE:
                    var circle = new Ellipse();
                    circle.Width = 50;
                    circle.Height = 50;
                    circle.Stroke = new SolidColorBrush(Colors.Black);
                    circle.Fill = new SolidColorBrush(Colors.LightGray);
                    pos.X = pos.X - circle.Width / 2;
                    pos.Y = pos.Y - circle.Height / 2;
                    BaseShape = circle;
                    break;

                case shapeType.LINE:
                    var line = new Line();
                    line.Stroke = new SolidColorBrush(Colors.Black);
                    line.X1 = pos.X;
                    line.Y1 = pos.Y;
                    line.X2 = pos.X;
                    line.Y2 = pos.Y;
                    BaseShape = line;
                    break;
            }
            Pos = pos;
        }
        public void SetPosition(double x, double y)
        {
            _pos.X = x;
            _pos.Y = y;
        }
    }
}
