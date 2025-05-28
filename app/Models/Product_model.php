<?php
namespace App\Models;
use CodeIgniter\Model;

class Product_model extends BaseModel {

    public function get_all_products() {
        return $this->db->get('product')->result();
    }

    public function get_product_price($product_id) {
        $this->db->select('unit_price');
        $this->db->where('product_id', $product_id);
        $query = $this->db->get('product');
        if ($query->num_rows() > 0) {
            return $query->row()->unit_price;
        }
        return 0;
    }
}
